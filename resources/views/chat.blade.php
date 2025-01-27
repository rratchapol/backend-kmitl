<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat View</title>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
</head>
<body>
    <h1>Realtime Chat</h1>
    <div id="chat-box">
        <!-- ข้อความแชทจะถูกแสดงใน div นี้ -->
    </div>

    <script>
        // เชื่อมต่อกับ Pusher
        const pusher = new Pusher('e5bdc31db695b897c05a', {
            cluster: 'ap1', // ใช้ cluster ที่ตั้งค่าไว้ใน Pusher
            encrypted: true
        });

        // Subscribe ไปยัง Channel ชื่อ 'chat'
        const channel = pusher.subscribe('chat');

        // ฟัง Event 'ChatMessageSent'
        channel.bind('ChatMessageSent', function(data) {
            console.log('Message received:', data);
            // แสดงข้อความใหม่ในหน้าจอ
            const chatBox = document.getElementById('chat-box');
            const messageElement = document.createElement('p');
            messageElement.textContent = `Buyer: ${data.buyer_id}, Seller: ${data.seller_id}, Message: ${data.message}`;
            chatBox.appendChild(messageElement);
        });

        echo.private('chat')
        .listen('ChatMessageSent', (event) => {
            console.log('Message received:', event);
            // ทำอะไรกับข้อความที่ได้รับ
            const chatBox = document.getElementById('chat-box');
            const messageElement = document.createElement('p');
            messageElement.textContent = `${event.message} - from ${event.buyer_id}`;
            chatBox.appendChild(messageElement);
    });
    </script>
</body>
</html>
