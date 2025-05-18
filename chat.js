// Clase para manejar el chat de IA
class CuncunulChat {
    constructor() {
        this.isMinimized = false;
        this.isOpen = false;
        this.messages = [];
        this.apiEndpoint = '/api/chat';
        
        this.init();
    }
    
    init() {
        this.createChatInterface();
        this.attachEventListeners();
        
        // Mostrar mensaje de bienvenida inicial
        this.addMessage('assistant', '¡Hola! Soy el asistente virtual de Cuncunul. ¿En qué puedo ayudarte hoy? 🌮');
    }
    
    createChatInterface() {
        // Botón flotante
        const floatButton = document.createElement('button');
        floatButton.className = 'chat-float-button show';
        floatButton.innerHTML = '💬';
        floatButton.id = 'chatFloatButton';
        document.body.appendChild(floatButton);
        
        // Contenedor del chat
        const chatContainer = document.createElement('div');
        chatContainer.className = 'chat-container';
        chatContainer.id = 'chatContainer';
        chatContainer.style.display = 'none';
        
        chatContainer.innerHTML = `
            <div class="chat-header" id="chatHeader">
                <h3>
                    <span>🌮 Asistente Cuncunul</span>
                    <button class="chat-toggle" id="chatToggle">▲</button>
                </h3>
            </div>
            <div class="chat-messages" id="chatMessages"></div>
            <div class="chat-input-area">
                <div class="chat-input-container">
                    <input 
                        type="text" 
                        class="chat-input" 
                        id="chatInput" 
                        placeholder="Escribe tu pregunta..."
                        maxlength="500"
                    >
                    <button class="chat-send-button" id="chatSendButton">
                        <span>➤</span>
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(chatContainer);
    }
    
    attachEventListeners() {
        const floatButton = document.getElementById('chatFloatButton');
        const chatContainer = document.getElementById('chatContainer');
        const chatToggle = document.getElementById('chatToggle');
        const chatInput = document.getElementById('chatInput');
        const sendButton = document.getElementById('chatSendButton');
        
        // Abrir chat
        floatButton.addEventListener('click', () => this.openChat());
        
        // Minimizar/maximizar chat
        chatToggle.addEventListener('click', () => this.toggleChat());
        
        // Enviar mensaje con Enter
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        // Enviar mensaje con botón
        sendButton.addEventListener('click', () => this.sendMessage());
        
        // Deshabilitar/habilitar botón según input
        chatInput.addEventListener('input', () => {
            const hasText = chatInput.value.trim().length > 0;
            sendButton.disabled = !hasText;
        });
    }
    
    openChat() {
        const floatButton = document.getElementById('chatFloatButton');
        const chatContainer = document.getElementById('chatContainer');
        
        floatButton.classList.remove('show');
        chatContainer.style.display = 'block';
        this.isOpen = true;
        
        // Focus en el input
        setTimeout(() => {
            document.getElementById('chatInput').focus();
        }, 300);
    }
    
    toggleChat() {
        const chatContainer = document.getElementById('chatContainer');
        const floatButton = document.getElementById('chatFloatButton');
        
        if (this.isMinimized) {
            // Maximizar
            chatContainer.classList.remove('minimized');
            this.isMinimized = false;
            setTimeout(() => {
                document.getElementById('chatInput').focus();
            }, 300);
        } else {
            // Minimizar o cerrar
            if (this.isOpen) {
                chatContainer.classList.add('minimized');
                this.isMinimized = true;
            } else {
                chatContainer.style.display = 'none';
                floatButton.classList.add('show');
                this.isOpen = false;
            }
        }
    }
    
    async sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        
        if (!message) return;
        
        // Agregar mensaje del usuario
        this.addMessage('user', message);
        input.value = '';
        document.getElementById('chatSendButton').disabled = true;
        
        // Mostrar indicador de carga
        this.showTypingIndicator();
        
        try {
            console.log("Enviando mensaje a:", this.apiEndpoint);
            // Enviar a la API
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: message })
            });
            
            const data = await response.json();
            console.log("Respuesta recibida:", data);
            
            // Remover indicador de carga
            this.hideTypingIndicator();
            
            if (data.success) {
                this.addMessage('assistant', data.message);
            } else {
                let errorMessage = 'Lo siento, ocurrió un error. ';
                if (data.error) {
                    errorMessage += 'Detalles: ' + data.error;
                    console.error('Error detallado:', data.error, data.debug || {});
                }
                this.addMessage('assistant', errorMessage);
            }
            
        } catch (error) {
            this.hideTypingIndicator();
            console.error('Error completo:', error);
            this.addMessage('assistant', 'Lo siento, no pude conectar con el servidor. Por favor verifica tu conexión. ' + (error.message || ''));
        }
    }
    
    addMessage(type, content) {
        const messagesContainer = document.getElementById('chatMessages');
        const messageElement = document.createElement('div');
        messageElement.className = `message ${type}`;
        
        const now = new Date();
        const time = now.toLocaleTimeString('es-MX', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        messageElement.innerHTML = `
            <div class="message-content">${this.formatMessage(content)}</div>
            <div class="message-time">${time}</div>
        `;
        
        messagesContainer.appendChild(messageElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Guardar mensaje
        this.messages.push({ type, content, time });
    }
    
    formatMessage(content) {
        // Convertir enlaces a HTML
        content = content.replace(
            /(https?:\/\/[^\s]+)/g, 
            '<a href="$1" target="_blank" style="color: #e3ba7e;">$1</a>'
        );
        
        // Convertir saltos de línea
        content = content.replace(/\n/g, '<br>');
        
        return content;
    }
    
    showTypingIndicator() {
        const messagesContainer = document.getElementById('chatMessages');
        const typingElement = document.createElement('div');
        typingElement.className = 'chat-loading';
        typingElement.id = 'chatTypingIndicator';
        
        typingElement.innerHTML = `
            <span style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">Escribiendo</span>
            <div class="chat-loading-dots">
                <div class="chat-loading-dot"></div>
                <div class="chat-loading-dot"></div>
                <div class="chat-loading-dot"></div>
            </div>
        `;
        
        messagesContainer.appendChild(typingElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    hideTypingIndicator() {
        const indicator = document.getElementById('chatTypingIndicator');
        if (indicator) {
            indicator.remove();
        }
    }
}

// Inicializar el chat cuando la página se carga
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si ya existe un chat
    if (!window.cuncunulChat) {
        window.cuncunulChat = new CuncunulChat();
    }
});

// Cerrar el chat al hacer click fuera de él
document.addEventListener('click', function(event) {
    const chatContainer = document.getElementById('chatContainer');
    const floatButton = document.getElementById('chatFloatButton');
    
    if (chatContainer && floatButton) {
        const isClickInsideChat = chatContainer.contains(event.target);
        const isClickOnFloat = floatButton.contains(event.target);
        
        if (!isClickInsideChat && !isClickOnFloat && chatContainer.style.display !== 'none') {
            // Solo cerrar si está abierto y no minimizado
            if (window.cuncunulChat && window.cuncunulChat.isOpen && !window.cuncunulChat.isMinimized) {
                window.cuncunulChat.toggleChat();
            }
        }
    }
});