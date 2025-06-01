// Helper: base64url to Uint8Array
function base64urlToBuffer(base64urlString) {
    // Convert base64url to base64 standard
    // Standard Base64 :- Pj4+Pz8/PDw8==
    // URL Base64:-       Pj4-Pz8_PDw8
    base64urlString = base64urlString.replace(/-/g, '+').replace(/_/g, '/');
    // Pad with '=' to length multiple of 4
    while (base64urlString.length % 4) {
        base64urlString += '=';
    }
    // decode the string from base64 to plain
    const str = atob(base64urlString);

    // creates a utf-8 byte array with str.length
    const buf = new Uint8Array(str.length);

    // loops through each character
    for (let i = 0; i < str.length; i++) {

        // creates the buffer bytes
        buf[i] = str.charCodeAt(i);
    }

    // returns the buffer
    return buf;
}

// Helper: Uint8Array to base64url
function bufferToBase64url(buffer) {
    // initializes an empty variable
    let str = '';

    // assigns a buffer from parameter
    const bytes = new Uint8Array(buffer);

    // loops through each byte in the buffer
    for (let i = 0; i < bytes.byteLength; i++) {

        // converts the ASCII-text of the character code.
        str += String.fromCharCode(bytes[i]);
    }
    // encodes the string to base64 standard
    let base64 = btoa(str);
    // convert to base64url
    base64 = base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    // returns the base64 URL encoded.
    return base64;
}

// function to handle passkey verification
async function loginWithPasskey(username) {
    // if username value is falsy (null, undefined, false)
    if (!username) {
        // shows an error message
        alert('Username is required for passkey login');

        // stops the script
        return;
    }

    // Fetch challenge and credentialId from backend
    const response = await fetch('php/login-challenge.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username })
    });

    // convert to json
    const challengeData = await response.json();

    // if challengeData.success if not a truthy value
    if (!challengeData.success) {
        // shows appropriate alert message
        alert('Error: ' + (challengeData.message || 'Failed to fetch challenge'));

        // stops the script
        return;
    }

    // create a publicKey object to fed to WebAuthn API (navigator.credentials.get)
    const credentialRequestOptions = {
        publicKey: {
            // challenge (received by the server)
            challenge: base64urlToBuffer(challengeData.publicKey.challenge),

            // allowed credentials
            allowCredentials: [{
                // id returned by server
                id: base64urlToBuffer(challengeData.publicKey.allowCredentials[0].id),

                // type of object
                type: "public-key"
            }],

            // maximum time
            timeout: 60000,


            // allows user to select from a number of devices
            userVerification: "preferred"
        }
    };

    // try-catch block
    try {

        // user solves the challenge returned by the server and value saved to assertion
        const assertion = await navigator.credentials.get(credentialRequestOptions);

        // sends the POST request to 'php/login-verify.php' endpoint
        // to verify the user's passkey
        const loginResult = await fetch('php/login-verify.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                // sends the required data to the server
                id: assertion.id,
                rawId: bufferToBase64url(assertion.rawId),
                type: assertion.type,
                response: {
                    authenticatorData: bufferToBase64url(assertion.response.authenticatorData),
                    clientDataJSON: bufferToBase64url(assertion.response.clientDataJSON),
                    signature: bufferToBase64url(assertion.response.signature),
                    userHandle: assertion.response.userHandle ? bufferToBase64url(assertion.response.userHandle) : null
                }
            })
        });

        // converts response to json
        const result = await loginResult.json();

        // if result.success is equal to true
        if (result.success) {

            // shows success message
            alert('Login successful!');

            // redirect to chat.php
            location.href = 'chat.php';
        } else {

            // otherwise, show error message
            alert('Login failed: ' + result.message);
        }
        // Incase of WebAuthn API isn't supported or miscellanious errors.
    } catch (err) {
        console.error(err);
        alert('Passkey login failed.');
    }
}