<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EduHelperAgentController extends Controller
{
    private $allowedTopics = ['solar system', 'fractions', 'water cycle'];

    private $keywords = [
        'solar system' => [
            // your existing keywords
            'planet', 'sun', 'moon', 'orbit', 'star', 'hottest', 'largest', 'venus', 'mars',
            'mercury', 'earth', 'jupiter', 'saturn', 'uranus', 'neptune', 'pluto',
            'dwarf planet', 'asteroid', 'comet', 'meteor', 'meteorite', 'galaxy', 'space', 
            'gravity', 'satellite', 'rings', 'solar system', 'equinox', 'solstice',
            'asteroid belt', 'Kuiper belt', 'Oort cloud', 'space probe', 'lunar eclipse',
            'solar eclipse', 'space station', 'cosmos', 'light year', 'astronomy', 'exoplanet',
    
            // added advanced topics
            'heliocentric', 'geocentric', 'nebula', 'supernova', 'black hole',
            'event horizon', 'cosmic dust', 'interstellar', 'nuclear fusion',
            'photosphere', 'chromosphere', 'corona', 'solar flare', 'sunspot',
            'celestial body', 'tidal locking', 'retrograde motion', 'planetary rotation',
            'planetary revolution', 'red giant', 'white dwarf', 'milky way',
            'big bang', 'cosmology', 'space-time', 'orbital velocity',
            'escape velocity', 'gravitational pull', 'space debris',
            'terraforming', 'astrobiology', 'spectroscopy', 'constellation',
    
            // NEW extra topics added
            'gas giant', 'ice giant', 'terrestrial planet', 'inner planets', 'outer planets',
            'planet atmosphere', 'magnetic field', 'aurora', 'solar wind', 'telescope',
            'hubble telescope', 'james webb', 'astronaut', 'space mission',
            'planet formation', 'habitable zone', 'axial tilt', 'day length',
            'year length', 'galactic center', 'cosmic microwave background'
        ],
    
        'fractions' => [
            // your existing keywords
            'half', 'quarter', 'percentage', 'numerator', 'denominator', 'fraction',
            'ratio', 'mixed number', 'improper fraction', 'equivalent', 'simplify',
            'decimal', 'percent', 'divide', 'multiply', 'add', 'subtract', 'part', 'whole',
            'reciprocal', 'common denominator', 'simplest form', 'fractional', 'unit fraction',
            'fraction comparison', 'fraction conversion', 'fraction operations', 'proportion',
    
            // added advanced topics
            'complex fraction', 'rational number', 'irrational number',
            'algebraic fraction', 'fraction inequality', 'cross multiplication',
            'like fractions', 'unlike fractions', 'proper fraction',
            'simplification', 'conversion', 'decimal expansion',
            'percentage increase', 'percentage decrease',
            'ratio analysis', 'scale factor', 'proportionality',
            'inverse proportion', 'direct proportion',
            'fractional exponent', 'fractional coefficient',
            'word problems', 'numerical reasoning',
    
            // NEW extra topics added
            'fraction model', 'number line fraction', 'visual fraction',
            'area model', 'unit rate', 'ratio table', 'percentage profit',
            'percentage loss', 'fraction equation', 'divide by fraction',
            'multiply by fraction', 'simplify ratio', 'fraction puzzle'
        ],
    
        'water cycle' => [
            // your existing keywords
            'evaporation', 'condensation', 'rain', 'precipitation', 'groundwater',
            'water cycle', 'humidity', 'transpiration', 'runoff', 'infiltration',
            'clouds', 'snow', 'ice', 'river', 'lake', 'ocean', 'groundwater recharge',
            'cycle', 'atmosphere', 'aquifer', 'glacier', 'melting', 'sublimation', 'dew',
            'fog', 'watershed', 'water table', 'storm', 'hydrology', 'water vapor', 'cloud formation',
    
            // added advanced topics
            'evapotranspiration', 'condensation nuclei', 'percolation',
            'capillary action', 'surface tension', 'water storage',
            'cryosphere', 'biosphere', 'atmospheric pressure',
            'climate change', 'weather patterns', 'monsoon',
            'groundwater depletion', 'freshwater', 'saltwater',
            'water purification', 'desalination', 'hydrological cycle',
            'cloud seeding', 'aquatic ecosystem', 'water reservoir',
            'precipitation intensity', 'flood', 'drought',
    
            // NEW extra topics added
            'rainwater harvesting', 'water conservation', 'air moisture',
            'solar radiation', 'temperature change', 'heat absorption',
            'hail', 'sleet', 'humidity level', 'atmospheric layers',
            'stormwater', 'river flow', 'ocean currents', 'salinity',
            'water pollution', 'sewage treatment', 'erosion',
            'greenhouse effect', 'thermal expansion of water'
        ]
    ];
    
    
    

    public function chat(Request $request)
    {
        $message = trim($request->message);

        $topic = $this->detectTopic($message);
        if (!$topic) {
            return response()->json([
                'reply' => "I can only help with Solar System, Fractions, or Water Cycle 😊"
            ]);
        }

        // Initialize session history
        if (!session()->has('history')) {
            session(['history' => [
                ['role' => 'system', 'content' => 'You are EduHelperAgent. Only answer questions about Solar System, Fractions, or Water Cycle. Reply max 60 words.']
            ]]);
        }

        session()->push('history', ['role' => 'user', 'content' => $message]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                'Content-Type'  => 'application/json'
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'      => 'llama-3.3-70b-versatile', // Supported Groq model
                'messages'   => session('history'),
                'max_tokens' => 60
            ]);

            if ($response->successful()) {
                $reply = $response->json('choices.0.message.content') ?? '⚠ No reply from AI';
                session()->push('history', ['role' => 'assistant', 'content' => $reply]);
            } else {
                $reply = "⚠ AI server error: " . $response->body();
            }

        } catch (\Exception $e) {
            $reply = "⚠ Error connecting to AI: " . $e->getMessage();
        }

        return response()->json(['reply' => $reply]);
    }

    // Detect topic based on keywords
    private function detectTopic($message)
    {
        foreach ($this->allowedTopics as $topic) {
            foreach ($this->keywords[$topic] as $word) {
                if (stripos($message, $word) !== false) return $topic;
            }
        }
        return null;
    }
}
