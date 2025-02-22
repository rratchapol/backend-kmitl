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
            cluster: 'ap1',
            encrypted: true
        });

        // Subscribe ไปยัง Channel ชื่อ 'chat.1.2'
        const channel = pusher.subscribe('chat.1.2'); // ใช้ชื่อ Channel ที่ตรงกับข้อมูลที่คุณส่งมา

        // ฟัง Event 'ChatMessageSent'
        channel.bind('ChatMessageSent', function(data) {
            console.log('Message received data:', data);
            // แสดงข้อความใหม่ในหน้าจอ
            const chatBox = document.getElementById('chat-box');
            const messageElement = document.createElement('p');
            messageElement.textContent = `sender: ${data.sender_id}, receiver: ${data.receiver_id}, Message: ${data.message}`;
            chatBox.appendChild(messageElement);
        });
    </script>
</body>
</html>
