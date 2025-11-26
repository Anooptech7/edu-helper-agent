<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Groq;

class GroqEduChatController extends Controller
{
    public function chat(Request $request)
    {
        $userMessage = trim($request->message);

        // Allowed topics
        $allowed = ['solar system', 'fractions', 'water cycle'];
        $allowedCheck = false;

        foreach ($allowed as $t) {
            if (stripos($userMessage, $t) !== false) {
                $allowedCheck = true;
                break;
            }
        }

        if (!$allowedCheck) {
            return response()->json([
                'reply' => "I can only help with Solar System, Fractions, or Water Cycle 😊"
            ]);
        }

        // History
        if (!session()->has('history')) {
            session(['history' => [
                [
                    "role" => "system",
                    "content" => "You are EduHelperAgent. You ONLY answer questions about Solar System, Fractions, or Water Cycle. Keep responses short and simple for school students."
                ]
            ]]);
        }

        session()->push('history', ["role" => "user", "content" => $userMessage]);

        try {
            $client = Groq::client(getenv("GROQ_API_KEY"));

            $response = $client->chat()->completions()->create([
                "model" => "llama-3.1-70b-versatile",
                "messages" => session('history'),
                "max_tokens" => 150,
            ]);

            $reply = $response->choices[0]->message->content;

            session()->push('history', ["role" => "assistant", "content" => $reply]);

        } catch (\Exception $e) {
            $reply = "Error talking to AI: " . $e->getMessage();
        }

        return response()->json(['reply' => $reply]);
    }
}
