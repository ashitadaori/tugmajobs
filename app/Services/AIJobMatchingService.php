<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use App\Models\JobSeekerProfile;
use App\Models\Job;
use Illuminate\Support\Facades\Http;

class AIJobMatchingService
{
    /**
     * Calculate match score between job seeker and job
     */
    public function calculateMatchScore(JobSeekerProfile $profile, Job $job)
    {
        try {
            // Try OpenAI embeddings first
            $profileEmbedding = $this->getEmbedding($this->prepareProfileText($profile));
            $jobEmbedding = $this->getEmbedding($this->prepareJobText($job));
            
            // Calculate cosine similarity between embeddings
            $similarity = $this->cosineSimilarity($profileEmbedding, $jobEmbedding);
            
            // Convert similarity to percentage score
            return round($similarity * 100);
        } catch (\Exception $e) {
            // Fallback to keyword-based matching if OpenAI fails
            \Log::warning('OpenAI API failed, using fallback matching: ' . $e->getMessage());
            return $this->calculateFallbackMatchScore($profile, $job);
        }
    }

    /**
     * Analyze skill gaps and provide learning recommendations
     */
    public function analyzeSkillGaps(JobSeekerProfile $profile, Job $job)
    {
        $prompt = $this->buildSkillGapPrompt($profile, $job);
        
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert career advisor. Analyze the skill gaps between the candidate\'s profile and job requirements, then suggest specific learning resources.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ]);

        return [
            'analysis' => $result->choices[0]->message->content,
            'missing_skills' => $this->extractMissingSkills($profile->skills, $job->requirements)
        ];
    }

    /**
     * Get embedding vector for text using OpenAI's embedding API
     */
    private function getEmbedding($text)
    {
        $response = OpenAI::embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $text,
        ]);

        return $response->embeddings[0]->embedding;
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    private function cosineSimilarity($vec1, $vec2)
    {
        $dot = 0;
        $norm1 = 0;
        $norm2 = 0;
        
        for ($i = 0; $i < count($vec1); $i++) {
            $dot += $vec1[$i] * $vec2[$i];
            $norm1 += $vec1[$i] * $vec1[$i];
            $norm2 += $vec2[$i] * $vec2[$i];
        }
        
        return $dot / (sqrt($norm1) * sqrt($norm2));
    }

    /**
     * Prepare profile text for embedding
     */
    private function prepareProfileText(JobSeekerProfile $profile)
    {
        return implode(" ", [
            implode(", ", $profile->skills),
            $profile->experience,
            implode(", ", $profile->education)
        ]);
    }

    /**
     * Prepare job text for embedding
     */
    private function prepareJobText(Job $job)
    {
        return implode(" ", [
            $job->title,
            $job->description,
            $job->requirements
        ]);
    }

    /**
     * Build prompt for skill gap analysis
     */
    private function buildSkillGapPrompt(JobSeekerProfile $profile, Job $job)
    {
        return "Compare the candidate's profile with job requirements and suggest learning resources:\n\n" .
               "Candidate Skills: " . implode(", ", $profile->skills) . "\n" .
               "Candidate Experience: " . $profile->experience . "\n" .
               "Job Requirements: " . $job->requirements . "\n" .
               "Job Description: " . $job->description;
    }

    /**
     * Extract missing skills by comparing profile skills with job requirements
     */
    private function extractMissingSkills(array $profileSkills, string $requirements)
    {
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Extract required skills from job requirements that are not present in the candidate\'s skill set. Return as comma-separated list.'
                ],
                [
                    'role' => 'user',
                    'content' => "Profile Skills: " . implode(", ", $profileSkills) . "\nJob Requirements: " . $requirements
                ]
            ]
        ]);

        $missingSkills = explode(",", $result->choices[0]->message->content);
        return array_map('trim', $missingSkills);
    }
} 