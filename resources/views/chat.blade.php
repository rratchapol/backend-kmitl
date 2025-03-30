<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat View</title>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        #chat-box {
            width: 400px;
            max-width: 100%;
            height: 400px;
            border: 1px solid #ccc;
            padding: 10px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .message {
            padding: 8px 12px;
            margin: 5px;
            border-radius: 15px;
            max-width: 70%;
        }
        .sent {
            background-color: #dcf8c6;
            align-self: flex-end;
        }
        .received {
            background-color: #f1f0f0;
            align-self: flex-start;
        }
    </style>
</head>
<body>
    <h1>Realtime Chat</h1>
    <div id="chat-box"></div>

    <script>
        // ตั้งค่าผู้ใช้ปัจจุบัน (เปลี่ยนเป็นค่า dynamic ได้)
        const currentUserId = 2; // สมมติว่าผู้ใช้ที่ล็อกอินคือ user_id = 1

        // เชื่อมต่อกับ Pusher
        const pusher = new Pusher('e5bdc31db695b897c05a', {
            cluster: 'ap1',
            encrypted: true
        });

        // Subscribe ไปยัง Channel 'chat.1.2'
        const channel = pusher.subscribe('chat.1.2'); // ใช้ชื่อ Channel ที่ตรงกับข้อมูลที่คุณส่งมา

        // ฟัง Event 'ChatMessageSent'
        channel.bind('ChatMessageSent', function(data) {
            console.log('Message received data:', data);

            const chatBox = document.getElementById('chat-box');
            const messageElement = document.createElement('div');
            messageElement.classList.add('message');

            if (data.sender_id === currentUserId) {
                messageElement.classList.add('sent'); // ผู้ส่งอยู่ขวา
            } else {
                messageElement.classList.add('received'); // ผู้รับอยู่ซ้าย
            }

            messageElement.textContent = `Message: ${data.message}`;
            chatBox.appendChild(messageElement);

            // เลื่อน Scroll ลงล่างสุดเพื่อแสดงข้อความล่าสุด
            chatBox.scrollTop = chatBox.scrollHeight;
        });

            // Subscribe ไปยังช่องแจ้งเตือน "อ่านแล้ว"
    const readChannel = pusher.subscribe('chat.read.' + currentUserId);
    readChannel.bind('ChatRead', function(data) {
        console.log('Message read:', data);
        document.querySelectorAll(`.read-status[data-id="${data.sender_id}"]`).forEach(el => {
            el.textContent = "✔ อ่านแล้ว"; // อัปเดตข้อความเป็น "อ่านแล้ว"
        });
    });
    </script>
</body>
</html>
