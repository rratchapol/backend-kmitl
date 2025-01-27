import './bootstrap';
// resources/js/app.js หรือไฟล์ JavaScript ที่ใช้
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'pusher',
    key: 'e5bdc31db695b897c05a',
    cluster: 'ap1',
    forceTLS: true
});

echo.private('chat')
    .listen('ChatMessageSent', (event) => {
        console.log('Message received:', event);
        // เพิ่มข้อความที่ได้รับในหน้า
        const chatBox = document.getElementById('chat-box');
        const messageElement = document.createElement('p');
        messageElement.textContent = `Message from buyer ${event.buyer_id}: ${event.message}`;
        chatBox.appendChild(messageElement);
    });