<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduHelperAgent Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f3f5; transition: background 0.3s, color 0.3s; }
        #chatBox { height: 400px; overflow-y: auto; padding: 15px; background: #fff; border-radius: 10px; border: 1px solid #dee2e6; transition: background 0.3s, color 0.3s; }
        .user-msg { color: #0d6efd; margin-bottom: 10px; animation: fadeIn 0.5s; }
        .bot-msg { color: #198754; margin-bottom: 10px; animation: fadeIn 0.5s; }
        @keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
        .btn-group { margin-top: 10px; }
    </style>
</head>
<body class="p-3">
<div class="container">
    <h2 class="mb-3">EduHelperAgent</h2>

    <!-- Chat Box -->
    <div id="chatBox" class="mb-3"></div>

    <!-- Input -->
    <div class="input-group mb-2">
        <input type="text" id="msg" class="form-control" placeholder="Ask a question...">
        <button class="btn btn-primary" onclick="sendMessage()">Send</button>
    </div>

    <!-- Buttons: Reset & Theme -->
    <div class="d-flex justify-content-between">
        <button class="btn btn-secondary" onclick="resetChat()">Reset Chat</button>
        <div>
            <button class="btn btn-dark me-1" onclick="setDarkTheme()">Dark</button>
            <button class="btn btn-light" onclick="setLightTheme()">Light</button>
        </div>
    </div>
</div>

<script>
    // Greeting message with topics
    const GREETING = `<div class="bot-msg"><b>Bot:</b> Hello! 👋 I can help you learn about <b>Solar System</b>, <b>Fractions</b>, or <b>Water Cycle</b>. What would you like to know?</div>`;

    // Show greeting on load
    window.onload = function() {
        let chatBox = document.getElementById('chatBox');
        chatBox.innerHTML = GREETING;
    };

    function sendMessage() {
        let message = document.getElementById('msg').value;
        if (!message.trim()) return;

        let chatBox = document.getElementById('chatBox');
        chatBox.innerHTML += `<div class="user-msg"><b>You:</b> ${message}</div>`;
        chatBox.scrollTop = chatBox.scrollHeight;

        fetch("{{ route('ask.groq') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            chatBox.innerHTML += `<div class="bot-msg"><b>Bot:</b> ${data.reply}</div>`;
            chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(err => {
            chatBox.innerHTML += `<div class="bot-msg text-danger"><b>Bot:</b> Error connecting to server</div>`;
        });

        document.getElementById('msg').value = "";
    }

    // Reset chat
    function resetChat() {
        let chatBox = document.getElementById('chatBox');
        chatBox.innerHTML = GREETING;
        document.getElementById('msg').value = "";

        // Optional server reset
        fetch("{{ route('ask.groq') }}", { 
            method: "POST", 
            headers: { 
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content 
            }, 
            body: JSON.stringify({ message: "/reset" }) 
        });
    }

    // Theme toggles
    function setDarkTheme() {
        document.body.style.background = "#121212";
        document.body.style.color = "#fff";
        document.getElementById('chatBox').style.background = "#1e1e1e";
        document.getElementById('chatBox').style.color = "#fff";
    }

    function setLightTheme() {
        document.body.style.background = "#f1f3f5";
        document.body.style.color = "#000";
        document.getElementById('chatBox').style.background = "#fff";
        document.getElementById('chatBox').style.color = "#000";
    }
</script>
</body>
</html>
