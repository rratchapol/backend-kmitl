import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// กำหนดค่า Pusher
window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'pusher',
    key: 'e5bdc31db695b897c05a',      // ใส่ Pusher App Key
    cluster: 'ap1',     // ใส่ Cluster เช่น 'ap1'
    forceTLS: true               // เปิดการเชื่อมต่อแบบ TLS
});

echo.connector.pusher.connection.bind('connected', function() {
    console.log('Connected to Pusher!');
});

// ฟังก์ชันสำหรับรับข้อความแบบ Realtime
echo.private('chat')             // ชื่อ Channel เช่น 'chat'
    .listen('ChatMessageSent', (event) => { // ชื่อ Event เช่น 'ChatMessageSent'
        console.log('Message received:', event.message);
    });

export default echo;
