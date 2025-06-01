// Grab username and set welcome message
const username = sessionStorage.getItem('username');

// element which shows the welcome message
const welcomeDiv = document.getElementById('welcomeMessage');

// if username is falsy (null, undefined, false)
if (!username) {
    // redirect back to index.php
    window.location = "index.php";
} else {
    // otherwise, show a welcome message to user
    welcomeDiv.textContent = `Welcome, ${username}!`;
}

// list of users
const usersUl = document.getElementById('usersUl');

// chatbox area
const chatBox = document.getElementById('chatBox');

// chatform
const chatForm = document.getElementById('chatForm');

// message input
const messageInput = document.getElementById('message');

// upload file button
const uploadBtn = document.getElementById('uploadBtn');

// file input <input type=file>
const fileInput = document.getElementById('fileInput');

// the userId of user who is chatting with by default null
let selectedUserId = null;

// username of selected User by default null
let selectedUserName = null;

// shared key by default null
let sharedKey = null;

// current user id by default null
let currentUserId = null;

// the interval in which users list will be fetched from the server by default null
let usersInterval = null;

// the interval in which messages will be fetched from the server by default null
let messagesInterval = null;

const translatedMessages = {}; // Holds the translated messages

// Fetch users list
async function fetchUsers() {

    // try-catch block for better error handling
    try {

        // makes a GET request to 'php/fetch_users.php'
        const res = await fetch('php/fetch_users.php');

        // store the response as JSON in users variable
        const users = await res.json();

        // the element where users will be displayed
        // setting it to '' initially (if already has some entriess)
        usersUl.innerHTML = '';

        // looping through each users Object
        users.forEach(user => {

            // creates a <li>
            const li = document.createElement('li');

            // displays the username of the user
            li.textContent = user.username;

            // adds an attribute dataset and setting value or user.id
            li.dataset.userid = user.id;

            // adds event listener to the created <li>
            li.addEventListener('click', () => {

                // calls the selectUser function with user's id and username as params
                selectUser(user.id, user.username);
            });

            // appends the <li> to the usersUl element <ul> or <ol>
            usersUl.appendChild(li);
        });
    } catch (err) {
        // if the server returned a response which isn't parseable into json
        // shows the error message
        console.error('Failed to fetch users:', err);
    }
}

// function to translate messages
// accpets a param
async function translateMessage(iconElement) {

    // gets the .msg-text of this iconElements parant element
    const msgSpan = iconElement.parentElement.querySelector('.msg-text');

    // the original message
    const originalText = msgSpan.textContent;

    // messages will be translated into English by default
    const targetLang = 'en';

    // try-catch for error handling
    try {

        // adds fa-spin to simulate a loading animation
        iconElement.classList.add('fa-spinner', 'fa-spin');

        // removes the fa-language class to hide the translate message icon
        iconElement.classList.remove('fa-language');

        // API to auto-detect the original message's language
        const detectRes = await fetch("https://ws.detectlanguage.com/0.2/detect", {
            // POST request
            method: "POST",
            // Send required headers
            headers: {
                "Content-Type": "application/json",
                "Authorization": "Bearer ac1c1aa0fbc20e2c0f5609bdc3e3fd1e"  // Our API Key
            },
            // sent as  with original Text in request body
            body: JSON.stringify({ q: originalText })
        });

        // gets the response and store it as a JSON response
        const detectData = await detectRes.json();

        // sourceLang = gets the language detected by the API
        const sourceLang = detectData.data?.detections?.[0]?.language || null;

        // if language detection fails
        if (!sourceLang) {
            // show the required message in the chat message
            msgSpan.textContent = '[Language detection failed]';

            // stops the script
            return;
        }

        // API used for translation
        // Parameters required :-
        // => q = original Message
        // => langpair = translateFrom|translateTto
        const translateRes = await fetch(`https://api.mymemory.translated.net/get?q=${encodeURIComponent(originalText)}&langpair=${sourceLang}|${targetLang}`);

        // converts to json
        const translateData = await translateRes.json();

        // if text is translated successfully && No Language DISTINCT errors
        if (translateData.responseData?.translatedText && translateData.responseData.translatedText !== "PLEASE SELECT TWO DISTINCT LANGUAGES") {

            // replace the original message with translated message
            msgSpan.textContent = translateData.responseData.translatedText;

            // Save translated message so it persists on refresh
            const msgDiv = iconElement.closest('div[data-msgid]');
            if (msgDiv) {
                // get the messageId
                const msgId = msgDiv.dataset.msgid;

                // store in translatedMessages
                // so when the messages are being fetched from the server
                // it doesn't tamper with the translated message
                translatedMessages[msgId] = translateData.responseData.translatedText;
            }
        } else {
            // shows error if translation fails
            msgSpan.textContent = '[Translation failed]';
        }
    } catch (err) {
        // shows error if translation fails
        // from the API end or invalid content-type or any error
        msgSpan.textContent = '[Translation error]';
        console.error('Translation error:', err);
    } finally {
        // final block to remove the spinner
        iconElement.classList.remove('fa-spinner', 'fa-spin');

        // show the translation message icon back
        iconElement.classList.add('fa-language');
    }
}

// Select user to chat with
// Takes two params id and name
async function selectUser(id, name) {

    // sets the selectedUserId to id
    selectedUserId = id;

    // sets the selectedUsername to username
    selectedUserName = name;

    // generate a sharedKey username the sender and reciever username
    sharedKey = await generateSharedKey(username, name);

    // gets the usersUl array
    // loops through each <li>
    // removes the 'active' class from it
    Array.from(usersUl.children).forEach(li => li.classList.remove('active'));

    // finds the <li> tag which is active
    const activeLi = [...usersUl.children].find(li => li.dataset.userid == id);

    // adds active class to it
    if (activeLi) activeLi.classList.add('active');

    // shows whom the user is chatting with
    chatBox.innerHTML = `<div><em>Chatting with ${name}</em></div>`;

    // Immediately fetch messages once on user select
    fetchMessages();

    // Clear old interval if any, and start new interval only if user is selected
    if (messagesInterval) clearInterval(messagesInterval);
    messagesInterval = setInterval(() => {
        // fetch messages every 2 secs
        if (selectedUserId) fetchMessages();
    }, 2000);
}

// Fetch messages
async function fetchMessages() {
    if (!selectedUserId) {
        chatBox.innerHTML = 'Select a user to start chatting.';
        return;
    }
    if (!currentUserId) return;

    try {
        const isNearBottom = (chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight) < 50;
        const res = await fetch(`php/fetch.php?receiver_id=${selectedUserId}`);
        const messages = await res.json();

        const oldScrollTop = chatBox.scrollTop;
        const oldScrollHeight = chatBox.scrollHeight;

        chatBox.innerHTML = '';

        messages.forEach(msg => {
            const isMine = msg.sender_id == currentUserId;
            const div = document.createElement('div');
            div.classList.add(isMine ? 'my-message' : 'their-message');
            div.dataset.msgid = msg.id;

            const time = formatTimeIST(msg.timestamp);

            const profileImg = (msg.profile_path && msg.profile_path !== 'NULL')
                ? `<img src="${msg.profile_path}" alt="Profile" style="width:30px; height:30px; border-radius:50%; margin-right:8px;">`
                : `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#4a90e2" style="margin-right:8px;">
                    <circle cx="12" cy="12" r="12" fill="#a3cef1"/>
                    <path fill="#fff" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>`;

            let displayedContent = '';

            if (translatedMessages[msg.id]) {
                displayedContent = translatedMessages[msg.id];
            } else if (sharedKey) {
                const decrypted = simpleDecrypt(msg.encrypted_msg, sharedKey);

                if (msg.type === 'file' && decrypted.startsWith('file::')) {
                    const parts = decrypted.split('::')[1].split('|');
                    const fileName = parts[0];
                    const fileHint = parts[1].toLowerCase();
                    const fileUrl = `uploads/${fileName}`; // Adjust this path if needed

                    if (fileHint.includes('image')) {
                        displayedContent = `<a href="${fileUrl}" target="_blank"><img src="${fileUrl}" alt="image" style="max-width: 200px; border-radius: 8px;"></a>`;
                    } else {
                        displayedContent = `
                            <div style="
                                display: flex;
                                align-items: center;
                                background-color: #f1f0f0;
                                border-radius: 10px;
                                padding: 10px;
                                max-width: 300px;
                                box-shadow: 0 1px 2px rgba(0,0,0,0.1);
                            ">
                                <i class="fas fa-file-alt" style="font-size: 30px; color: #4a90e2; margin-right: 10px;"></i>
                                <div style="flex: 1;">
                                    <div style="font-weight: bold; color: #333;">${fileName}</div>
                                    <div style="font-size: 12px; color: #777;">Document</div>
                                </div>
                                <a href="${fileUrl}" download="${fileName}" style="margin-left: 10px; color: #4a90e2;">
                                    <i class="fas fa-download" style="font-size: 18px;"></i>
                                </a>
                            </div>
                        `;

                    }
                } else {
                    displayedContent = decrypted;
                }
            } else {
                displayedContent = '[No key]';
            }

            div.innerHTML = `
                ${profileImg}
                <div>
                    <div class="msg-content">
                        <b>${msg.sender_name}</b>: 
                        <span class="msg-text">${displayedContent}</span>
                        ${msg.type === 'text' ? `
                            <i class="fas fa-language translate-btn" 
                            title="Translate" 
                            style="margin-left: 8px; cursor: pointer; display: inline;" 
                            onclick="translateMessage(this)">
                            </i>` : ''}
                    </div>
                    <div class="msg-meta">
                        <span class="msg-time">${time}</span>
                        ${isMine ? `<button class="delete-btn" onclick="confirmDelete(${msg.id})"><i class="fas fa-trash"></i></button>` : ''}
                    </div>
                </div>
            `;

            chatBox.appendChild(div);
        });

        if (isNearBottom) {
            chatBox.scrollTop = chatBox.scrollHeight;
        } else {
            chatBox.scrollTop = oldScrollTop + (chatBox.scrollHeight - oldScrollHeight);
        }
    } catch (err) {
        console.error('Failed to fetch messages:', err);
    }
}


// Send message
chatForm.addEventListener('submit', async e => {
    e.preventDefault();

    if (!selectedUserId) {
        alert('Please select a user to chat with!');
        return;
    }

    const message = messageInput.value.trim();
    if (!message) return;

    if (!sharedKey) {
        alert("Shared key not available.");
        return;
    }

    const encryptedMsg = simpleEncrypt(message, sharedKey);

    try {
        await fetch('php/send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: encryptedMsg,
                receiver_id: selectedUserId,
                sender_id: currentUserId
            })
        });
        messageInput.value = '';
        fetchMessages();
    } catch (err) {
        console.error('Failed to send message:', err);
    }
});

uploadBtn.addEventListener('click', (e) => {
    fileInput.click();
});

fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'
    ];
    const fileName = file.name;
    const fileExtension = fileName.split('.').pop().toLowerCase();

    if (!allowedExtensions.includes(fileExtension)) {
        alert('Invalid file type! Please upload an image or document file.');
        fileInput.value = ''; // Clear the input
        return;
    }

    // Optional: You can confirm here before uploading
    console.log('Valid file selected:', fileName);

    // Prepare the form data
    const formData = new FormData();
    formData.append('file', file);

    // You can dynamically get the receiver_id from your app
    const receiverId = selectedUserId;
    formData.append('receiver_id', receiverId);

    // Send the request
    fetch('php/upload.php', {
        method: 'POST',
        body: formData,
        credentials: 'include' // Important to send cookies/session
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('File uploaded and message sent!');
                console.log('Response:', data);
            } else {
                alert('Error: ' + data.error);
                console.error('Server error:', data);
            }
        })
        .catch(err => {
            console.error('Upload failed:', err);
            alert('An error occurred during upload.');
        });
});

// Helpers for base64 and simple XOR encryption
function utf8ToB64(str) {
    return btoa(
        encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
            (match, p1) => String.fromCharCode('0x' + p1)
        )
    );
}

function b64ToUtf8(str) {
    return decodeURIComponent(atob(str).split('').map(c =>
        '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
    ).join(''));
}

function simpleEncrypt(text, key) {
    let result = '';
    for (let i = 0; i < text.length; i++) {
        result += String.fromCharCode(text.charCodeAt(i) ^ key.charCodeAt(i % key.length));
    }
    return utf8ToB64(result);
}

function simpleDecrypt(enc, key) {
    try {
        const decoded = b64ToUtf8(enc);
        let result = '';
        for (let i = 0; i < decoded.length; i++) {
            result += String.fromCharCode(decoded.charCodeAt(i) ^ key.charCodeAt(i % key.length));
        }
        return result;
    } catch {
        return '[Decryption error]';
    }
}

// Delete message
async function deleteMessage(messageId) {
    try {
        const res = await fetch('php/delete_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `message_id=${encodeURIComponent(messageId)}`
        });
        const data = await res.json();
        if (data.success) {
            fetchMessages();
        } else {
            alert('Failed to delete message: ' + (data.error || 'Unknown error'));
        }
    } catch (err) {
        alert('Error deleting message: ' + err.message);
    }
}

function confirmDelete(messageId) {
    if (confirm('Are you sure you want to delete this message?')) {
        deleteMessage(messageId);
    }
}

// Format time to IST and 12-hour clock
function formatTimeIST(datetime) {
    const date = new Date(datetime);
    return date.toLocaleString('en-IN', {
        timeZone: 'Asia/Kolkata',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

// Generate shared key using SHA-256 digest
async function generateSharedKey(username1, username2) {
    const sorted = [username1, username2].sort();
    const encoder = new TextEncoder();
    const data = encoder.encode(sorted[0] + ':' + sorted[1]);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashStr = hashArray.map(b => String.fromCharCode(b)).join('');
    return btoa(hashStr);
}

// Initialize app: get current user id, then start fetching users and messages
(async () => {
    try {
        const res = await fetch('php/get_userinfo.php');
        const data = await res.json();
        currentUserId = data.user_id;
        sessionStorage.setItem('user_id', currentUserId);

        // Initial fetch of users
        await fetchUsers();

        // Set interval for users list only (independent of chat)
        usersInterval = setInterval(fetchUsers, 2000);

        // Messages interval will start once user selects a chat, managed inside selectUser()

        chatBox.innerHTML = 'Select a user to start chatting.';
    } catch (err) {
        console.error('Failed to initialize user info:', err);
    }
})();