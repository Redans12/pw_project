/**
 * Gestor centralizado de peticiones AJAX para el sistema de reservaciones de Cuncunul
 */

// Función para realizar peticiones AJAX genéricas
function ajaxRequest(url, method, data, successCallback, errorCallback) {
    console.log("Iniciando solicitud AJAX a:", url);
    console.log("Datos a enviar:", data);
    
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        console.log("Respuesta recibida. Status:", xhr.status);
        console.log("Texto de respuesta:", xhr.responseText);
        
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const response = JSON.parse(xhr.responseText);
                console.log("Respuesta parseada:", response);
                successCallback(response);
            } catch (e) {
                console.error("Error al parsear JSON:", e);
                // Si la respuesta no es JSON, pasar la respuesta en texto plano
                successCallback(xhr.responseText);
            }
        } else {
            console.error("Error en la solicitud. Status:", xhr.status);
            if (errorCallback) {
                errorCallback(xhr.statusText);
            }
        }
    };
    
    xhr.onerror = function() {
        console.error("Error de conexión");
        if (errorCallback) {
            errorCallback('Error de conexión');
        }
    };
    
    xhr.send(data);
}

// Función para convertir un objeto a formato URL-encoded
function objectToFormData(obj) {
    const formData = Object.keys(obj)
        .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(obj[key]))
        .join('&');
    
    console.log("Form data generado:", formData);
    return formData;
}

// Función para iniciar sesión
function login(email, password, successCallback, errorCallback) {
    console.log("Iniciando login para:", email);
    
    const data = objectToFormData({
        email: email,
        password: password
    });
    
    ajaxRequest('ajax_login.php', 'POST', data, successCallback, errorCallback);
}

// Función para registrar un nuevo usuario
function register(name, email, password, confirmPassword, successCallback, errorCallback) {
    console.log("Iniciando registro para:", email);
    
    const data = objectToFormData({
        name: name,
        email: email,
        password: password,
        confirm_password: confirmPassword
    });
    
    ajaxRequest('ajax_register.php', 'POST', data, successCallback, errorCallback);
}

// Función para procesar una reservación
function processReservation(fecha, hora, personas, telefono, comentarios, successCallback, errorCallback) {
    console.log("Procesando reservación para fecha:", fecha);
    
    const data = objectToFormData({
        fecha: fecha,
        hora: hora,
        personas: personas,
        telefono: telefono,
        comentarios: comentarios
    });
    
    ajaxRequest('ajax_process_reservation.php', 'POST', data, successCallback, errorCallback);
}

// Función para mostrar mensajes de notificación
function showNotification(message, type = 'success') {
    console.log("Mostrando notificación:", message, "Tipo:", type);
    
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Añadir al DOM
    document.body.appendChild(notification);
    
    // Mostrar con animación
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Eliminar después de 3 segundos
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}