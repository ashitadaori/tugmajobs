<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
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
            // Try Gemini embeddings first
            $profileEmbedding = $this->getEmbedding($this->prepareProfileText($profile));
            $jobEmbedding = $this->getEmbedding($this->prepareJobText($job));

            // Calculate cosine similarity between embeddings
            $similarity = $this->cosineSimilarity($profileEmbedding, $jobEmbedding);

            // Convert similarity to percentage score
            return round($similarity * 100);
        } catch (\Exception $e) {
            // Fallback to keyword-based matching if Gemini fails
            \Log::warning('Gemini API failed, using fallback matching: ' . $e->getMessage());
            return $this->calculateFallbackMatchScore($profile, $job);
        }
    }

    /**
     * Analyze skill gaps and provide learning recommendations
     */
    public function analyzeSkillGaps(JobSeekerProfile $profile, Job $job)
    {
        $prompt = $this->buildSkillGapPrompt($profile, $job);

        $result = Gemini::generativeModel(model: 'models/gemini-2.0-flash')
            ->generateContent([
                'You are an expert career advisor. Analyze the skill gaps between the candidate\'s profile and job requirements, then suggest specific learning resources.',
                $prompt
            ]);

        return [
            'analysis' => $result->text(),
            'missing_skills' => $this->extractMissingSkills($profile->skills, $job->requirements)
        ];
    }

    /**
     * Get embedding vector for text using Gemini's embedding API
     */
    private function getEmbedding($text)
    {
        $response = Gemini::embeddingModel(model: 'text-embedding-004')
            ->embedContent($text);

        return $response->embedding->values;
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
            implode(", ", $profile->skills ?? []),
            $profile->experience ?? '',
            implode(", ", $profile->education ?? [])
        ]);
    }

    /**
     * Prepare job text for embedding
     */
    private function prepareJobText(Job $job)
    {
        return implode(" ", [
            $job->title ?? '',
            $job->description ?? '',
            $job->requirements ?? ''
        ]);
    }

    /**
     * Build prompt for skill gap analysis
     */
    private function buildSkillGapPrompt(JobSeekerProfile $profile, Job $job)
    {
        return "Compare the candidate's profile with job requirements and suggest learning resources:\n\n" .
               "Candidate Skills: " . implode(", ", $profile->skills ?? []) . "\n" .
               "Candidate Experience: " . ($profile->experience ?? 'None') . "\n" .
               "Job Requirements: " . ($job->requirements ?? 'None') . "\n" .
               "Job Description: " . ($job->description ?? 'None');
    }

    /**
     * Extract missing skills by comparing profile skills with job requirements
     */
    private function extractMissingSkills(array $profileSkills, string $requirements)
    {
        $result = Gemini::generativeModel(model: 'models/gemini-2.0-flash')
            ->generateContent([
                'Extract required skills from job requirements that are not present in the candidate\'s skill set. Return as comma-separated list only, no explanation.',
                "Profile Skills: " . implode(", ", $profileSkills) . "\nJob Requirements: " . $requirements
            ]);

        $missingSkills = explode(",", $result->text());
        return array_map('trim', $missingSkills);
    }

    /**
     * Fallback matching when AI is unavailable
     */
    private function calculateFallbackMatchScore(JobSeekerProfile $profile, Job $job)
    {
        $profileSkills = array_map('strtolower', $profile->skills ?? []);
        $requirements = strtolower($job->requirements ?? '');

        if (empty($profileSkills) || empty($requirements)) {
            return 50; // Default score when data is insufficient
        }

        $matchedSkills = 0;
        foreach ($profileSkills as $skill) {
            if (str_contains($requirements, $skill)) {
                $matchedSkills++;
            }
        }

        $score = count($profileSkills) > 0
            ? ($matchedSkills / count($profileSkills)) * 100
            : 50;

        return round($score);
    }
}
