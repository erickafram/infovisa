<?php
// session_start();
?>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Seu CSS e outras tags head aqui -->
</head>

<div class="chat-container">
    <div class="chat-box">
        <div class="chat-header">
            <i class="fas fa-headset icon"></i> Chat de Suporte
            <span class="status-online">Online</span>
            <button class="chat-toggle" onclick="toggleChat()"><i class="fas fa-minus"></i></button>
        </div>
        <div id="chat-body" class="chat-body"></div>
        <div class="chat-footer">
            <input type="text" id="chat-input" placeholder="Digite sua mensagem aqui..." />
            <button id="chat-send" onclick="sendMessage()">Enviar</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatBody = document.getElementById('chat-body');
    const chatFooter = document.querySelector('.chat-footer');
    const toggleButton = document.querySelector('.chat-toggle i');
    const userName = "<?php echo htmlspecialchars($_SESSION['user']['nome_completo']); ?>";

    // Load chat history from localStorage
    if (localStorage.getItem('chatHistory')) {
        chatBody.innerHTML = localStorage.getItem('chatHistory');
        chatBody.scrollTop = chatBody.scrollHeight;
    } else {
        const initialMessage = `Olá, ${userName}. Aqui você pode consultar informações de estabelecimento e processo. Por favor, inicie digitando o nome do estabelecimento ou CNPJ do estabelecimento.`;
        typeEffect(initialMessage, 'bot-message');
    }

    // Load chat minimized state from localStorage
    if (localStorage.getItem('chatMinimized') === 'true') {
        chatBody.classList.add('hidden');
        chatFooter.classList.add('hidden');
        toggleButton.classList.remove('fa-minus');
        toggleButton.classList.add('fa-plus');
    }
});

function typeEffect(text, className) {
    const chatBody = document.getElementById('chat-body');
    const botMessage = document.createElement('div');
    botMessage.className = className;
    chatBody.appendChild(botMessage);

    let i = 0;
    const typingSpeed = 20; // Velocidade de digitação aumentada

    function typeWriter() {
        if (i < text.length) {
            botMessage.textContent += text.charAt(i);
            i++;
            chatBody.scrollTop = chatBody.scrollHeight; // Descer ao escrever
            setTimeout(typeWriter, typingSpeed);
        } else {
            chatBody.scrollTop = chatBody.scrollHeight;
            saveChatHistory();
        }
    }
    typeWriter();
}

function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value;
    if (message.trim() === '') return;

    const chatBody = document.getElementById('chat-body');
    const userMessage = document.createElement('div');
    userMessage.textContent = message;
    userMessage.className = 'user-message';
    chatBody.appendChild(userMessage);
    chatBody.scrollTop = chatBody.scrollHeight; // Descer ao adicionar nova mensagem
    saveChatHistory();

    // Enviar a mensagem para o servidor via AJAX
    fetch('../Chat/chat_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message })
    })
    .then(response => response.json())
    .then(data => {
        typeEffect(data.reply, 'bot-message');
    });

    input.value = '';
}

function toggleChat() {
    const chatBody = document.getElementById('chat-body');
    const chatFooter = document.querySelector('.chat-footer');
    const toggleButton = document.querySelector('.chat-toggle i');
    chatBody.classList.toggle('hidden');
    chatFooter.classList.toggle('hidden');
    toggleButton.classList.toggle('fa-minus');
    toggleButton.classList.toggle('fa-plus');

    // Save chat minimized state to localStorage
    localStorage.setItem('chatMinimized', chatBody.classList.contains('hidden'));
}

function saveChatHistory() {
    const chatBody = document.getElementById('chat-body');
    localStorage.setItem('chatHistory', chatBody.innerHTML);
}
</script>

<style>
.chat-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 350px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    font-family: Arial, sans-serif;
    z-index: 9999; /* Adicione esta linha para que o chat-container fique acima de todos os outros elementos */
}

.chat-box {
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
}
.chat-header {
    background-color: #007bff;
    color: #fff;
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.chat-header .icon {
    font-size: 24px;
    margin-right: 10px;
}
.chat-header .status-online {
    background-color: #28a745;
    color: #fff;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 12px;
}
.chat-header .chat-toggle {
    background: none;
    border: none;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
}
.chat-body {
    height: 200px;
    overflow-y: auto;
    padding: 10px;
}
.chat-footer {
    display: flex;
    padding: 10px;
    background-color: #f1f1f1;
}
#chat-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-right: 10px;
}
#chat-send {
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 10px;
    cursor: pointer;
}
.user-message {
    background-color: #e0f7fa;
    padding: 10px;
    margin: 5px 0;
    border-radius: 10px;
    text-align: right;
}
.bot-message {
    background-color: #f1f8e9;
    padding: 10px;
    margin: 5px 0;
    border-radius: 10px;
    text-align: left;
    white-space: pre-line; /* Preserva quebras de linha */
}
.hidden {
    display: none;
}
</style>
