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
            <button id="chat-send" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
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
        const initialMessage = `Olá, ${userName}. Sou a AnaVisa. Aqui você pode consultar informações de estabelecimento e processo.\n\nPor favor, escolha uma das opções abaixo para continuar:\n1. Consultar estabelecimento e saber andamento do processo\n2. Saber quais documentos são necessários para o processo de licenciamento sanitário.\n3. Tire suas dúvidas.`;
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
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Adicionado para melhorar a sombra */
    border-radius: 10px; /* Adicionado para cantos arredondados */
    overflow: hidden; /* Adicionado para evitar conteúdo transbordando */
    font-family: Arial, sans-serif;
    z-index: 9999; /* Adicionado para que o chat-container fique acima de todos os outros elementos */
}

.chat-box {
    background-color: #fff;
}

.chat-header {
    background-color: #007bff;
    color: #fff;
    padding: 15px; /* Aumentado para melhor espaçamento */
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top-left-radius: 10px; /* Adicionado para arredondar os cantos */
    border-top-right-radius: 10px; /* Adicionado para arredondar os cantos */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Adicionado para sombra */
}

.chat-header .icon {
    font-size: 24px;
    margin-right: 10px;
}

.chat-header .status-online {
    background-color: #28a745;
    color: #fff;
    border-radius: 12px; /* Aumentado para melhor aparência */
    padding: 5px 10px; /* Aumentado para melhor espaçamento */
    font-size: 14px; /* Aumentado para melhor legibilidade */
}

.chat-header .chat-toggle {
    background: none;
    border: none;
    color: #fff;
    font-size: 20px; /* Aumentado para maior clique */
    cursor: pointer;
}

.chat-body {
    height: 300px; /* Aumentado para mais espaço de visualização */
    overflow-y: auto;
    padding: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Adicionado para sombra */
}

.chat-footer {
    display: flex;
    padding: 10px;
    background-color: #f1f1f1;
    border-bottom-left-radius: 10px; /* Adicionado para arredondar os cantos */
    border-bottom-right-radius: 10px; /* Adicionado para arredondar os cantos */
    box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.1); /* Adicionado para sombra */
}

#chat-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-right: 10px;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1); /* Adicionado para efeito de sombra interna */
}

#chat-send {
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 10px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Adicionado para sombra */
}

#chat-send i {
    font-size: 16px; /* Ajusta o tamanho do ícone */
}

.user-message {
    background-color: #e0f7fa;
    padding: 10px;
    margin: 5px 0;
    border-radius: 10px;
    text-align: right;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Adicionado para sombra */
}

.bot-message {
    background-color: #f1f8e9;
    padding: 10px;
    margin: 5px 0;
    border-radius: 10px;
    text-align: left;
    white-space: pre-line; /* Preserva quebras de linha */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Adicionado para sombra */
}

.hidden {
    display: none;
}
</style>
