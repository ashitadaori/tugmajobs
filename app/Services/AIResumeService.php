<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use App\Models\Jobseeker;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AIResumeService
{
    /**
     * Generate an optimized resume based on user profile and target job
     */
    public function generateResume(Jobseeker $profile, ?Job $targetJob = null): string
    {
        try {
            $prompt = $this->buildResumePrompt($profile, $targetJob);

            $result = Gemini::generativeModel(model: 'models/gemini-2.0-flash')
                ->generateContent([
                    'You are an expert resume writer and career counselor. Create a professional resume that highlights relevant skills and experience. Format it cleanly with clear sections.',
                    $prompt
                ]);

            return $result->text();
        } catch (\Exception $e) {
            Log::error('Error generating resume: ' . $e->getMessage());
            throw new \Exception('Failed to generate resume. Please try again later.');
        }
    }

    /**
     * Analyze resume and provide optimization suggestions
     */
    public function analyzeResume(string $resumeText): array
    {
        try {
            $result = Gemini::generativeModel(model: 'models/gemini-2.0-flash')
                ->generateContent([
                    'You are an expert resume reviewer. Analyze the resume and provide specific, actionable suggestions for improvement. Return your analysis as a JSON object with these keys: score (0-100), strengths (array), weaknesses (array), suggestions (array), keywords_missing (array).',
                    "Please analyze this resume and provide specific suggestions for improvement: \n\n" . $resumeText
                ]);

            $text = $result->text();

            // Try to parse as JSON
            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $parsed = json_decode($matches[0], true);
                if ($parsed) {
                    return $parsed;
                }
            }

            return [
                'score' => null,
                'analysis' => $text,
                'suggestions' => [],
            ];
        } catch (\Exception $e) {
            Log::error('Error analyzing resume: ' . $e->getMessage());
            throw new \Exception('Failed to analyze resume. Please try again later.');
        }
    }

    /**
     * Generate a tailored resume for a specific job
     */
    public function tailorResumeForJob(Jobseeker $profile, Job $job): array
    {
        try {
            $prompt = $this->buildTailoredResumePrompt($profile, $job);

            $result = Gemini::generativeModel(model: 'models/gemini-2.0-flash')
                ->generateContent([
                    'You are an expert resume writer specializing in ATS optimization. Create a tailored resume that maximizes relevance for the specific job. Highlight matching skills and experience. Return as JSON with keys: resume (string), match_score (0-100), highlighted_skills (array), suggested_additions (array).',
                    $prompt
                ]);

            $text = $result->text();

            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $parsed = json_decode($matches[0], true);
                if ($parsed) {
                    return $parsed;
                }
            }

            return [
                'resume' => $text,
                'match_score' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Error tailoring resume: ' . $e->getMessage());
            throw new \Exception('Failed to tailor resume. Please try again later.');
        }
    }

    /**
     * Generate cover letter for a job application
     */
    public function generateCoverLetter(Jobseeker $profile, Job $job): string
    {
        try {
            $user = $profile->user;

            $prompt = "Write a professional cover letter for:\n\n";
            $prompt .= "Candidate: {$user->name}\n";
            $prompt .= "Skills: " . ($profile->skills ?? 'Not specified') . "\n";
            $prompt .= "Experience: " . ($profile->experience ?? 'Not specified') . "\n\n";
            $prompt .= "Applying for: {$job->title}\n";
            $prompt .= "Company: " . ($job->employer->company_name ?? 'the company') . "\n";
            $prompt .= "Job Description: " . substr($job->description, 0, 1000) . "\n";
            $prompt .= "Requirements: " . ($job->requirements ?? 'Not specified') . "\n";

            $result = Gemini::generativeModel(model: 'models/gemini-2.0-flash')
                ->generateContent([
                    'You are an expert career counselor. Write a compelling, personalized cover letter that highlights the candidate\'s relevant experience and enthusiasm for the role. Keep it professional but engaging, around 300 words.',
                    $prompt
                ]);

            return $result->text();
        } catch (\Exception $e) {
            Log::error('Error generating cover letter: ' . $e->getMessage());
            throw new \Exception('Failed to generate cover letter. Please try again later.');
        }
    }

    /**
     * Get improvement suggestions for a specific section
     */
    public function improveSectionSuggestions(string $section, string $content): array
    {
        try {
            $result = Gemini::generativeModel(model: 'models/gemini-2.0-flash')
                ->generateContent([
                    "You are a resume expert. Analyze this {$section} section and provide 3-5 specific improvements. Return as JSON array of suggestion objects with keys: original (snippet), improved (rewritten version), reason (why it's better).",
                    "Section: {$section}\nContent:\n{$content}"
                ]);

            $text = $result->text();

            if (preg_match('/\[.*\]/s', $text, $matches)) {
                $parsed = json_decode($matches[0], true);
                if ($parsed) {
                    return $parsed;
                }
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error getting section suggestions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Extract keywords from a job posting
     */
    public function extractJobKeywords(Job $job): array
    {
        try {
            $content = $job->title . "\n" . $job->description . "\n" . ($job->requirements ?? '') . "\n" . ($job->qualifications ?? '');

            $result = Gemini::generativeModel(model: 'models/gemini-2.0-flash')
                ->generateContent([
                    'Extract the most important keywords and skills from this job posting. Return as JSON with keys: technical_skills (array), soft_skills (array), qualifications (array), industry_keywords (array).',
                    $content
                ]);

            $text = $result->text();

            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $parsed = json_decode($matches[0], true);
                if ($parsed) {
                    return $parsed;
                }
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error extracting keywords: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate ATS compatibility score
     */
    public function calculateATSScore(string $resumeText, Job $job): array
    {
        try {
            $jobKeywords = $this->extractJobKeywords($job);

            $result = Gemini::generativeModel(model: 'models/gemini-2.0-flash')
                ->generateContent([
                    'You are an ATS (Applicant Tracking System) expert. Analyze how well this resume matches the job requirements. Return JSON with: score (0-100), matched_keywords (array), missing_keywords (array), formatting_issues (array), recommendations (array).',
                    "Resume:\n{$resumeText}\n\nJob Keywords:\n" . json_encode($jobKeywords)
                ]);

            $text = $result->text();

            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $parsed = json_decode($matches[0], true);
                if ($parsed) {
                    return $parsed;
                }
            }

            return ['score' => null, 'analysis' => $text];
        } catch (\Exception $e) {
            Log::error('Error calculating ATS score: ' . $e->getMessage());
            return ['score' => null, 'error' => 'Failed to calculate ATS score'];
        }
    }

    /**
     * Build resume generation prompt
     */
    private function buildResumePrompt(Jobseeker $profile, ?Job $targetJob): string
    {
        $user = $profile->user;

        $prompt = "Create a professional resume for a candidate with the following background:\n\n";
        $prompt .= "Name: " . ($user->name ?? 'Candidate') . "\n";
        $prompt .= "Professional Summary: " . ($profile->professional_summary ?? 'Not provided') . "\n";
        $prompt .= "Skills: " . ($profile->skills ?? 'Not provided') . "\n";
        $prompt .= "Experience: " . ($profile->experience ?? 'No experience provided') . "\n";
        $prompt .= "Education: " . ($profile->education ?? 'Not provided') . "\n";
        $prompt .= "Years of Experience: " . ($profile->experience_years ?? 'Not specified') . "\n";

        if ($targetJob) {
            $prompt .= "\nTarget Job Details:\n";
            $prompt .= "Title: " . $targetJob->title . "\n";
            $prompt .= "Requirements: " . ($targetJob->requirements ?? 'Not specified') . "\n";
            $prompt .= "Description: " . substr($targetJob->description, 0, 500) . "\n";
        }

        return $prompt;
    }

    /**
     * Build tailored resume prompt
     */
    private function buildTailoredResumePrompt(Jobseeker $profile, Job $job): string
    {
        $prompt = $this->buildResumePrompt($profile, $job);
        $prompt .= "\n\nIMPORTANT: Tailor this resume specifically for the job listed above. ";
        $prompt .= "Highlight matching skills and experience. Use keywords from the job description. ";
        $prompt .= "Format for ATS compatibility.";

        return $prompt;
    }
}
