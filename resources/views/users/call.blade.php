<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voice Call Interface</title>
    <script src="https://speaklar.com/speaklar.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">
    <style>
        :root {
            --primary-color: #4285f4;
            --success-color: #34a853;
            --danger-color: #ea4335;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .call-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .language-select {
            position: relative;
            width: 100%;
        }

        .language-select select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            background-color: white;
            font-size: 16px;
            appearance: none;
            cursor: pointer;
        }

        .language-select::after {
            content: "▼";
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: #666;
            pointer-events: none;
        }

        .phone-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            box-sizing: border-box;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
        }

        .btn-call {
            background-color: var(--success-color);
            color: white;
            box-shadow: 0 4px 0 rgba(40, 167, 69, 0.3);
        }

        .btn-call:hover {
            background-color: #2d9245;
            transform: translateY(-2px);
        }

        .btn-hangup {
            background-color: var(--danger-color);
            color: white;
            box-shadow: 0 4px 0 rgba(220, 53, 69, 0.3);
            display: none;
        }

        .btn-hangup:hover {
            background-color: #d62a3e;
            transform: translateY(-2px);
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }

        audio {
            display: none;
        }

        .flag-icon {
            margin-right: 8px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="call-container">
        <h1>Voice Call</h1>

        <div class="input-group">
            <label for="languageSelect">Language</label>
            <div class="language-select">
                <select id="languageSelect" onchange="changeLanguage()">
                    <option value="bangla">
                        <span class="flag-icon flag-icon-bd"></span> Bangla
                    </option>
                    <option value="english">
                        <span class="flag-icon flag-icon-us"></span> English
                    </option>
                </select>
            </div>
        </div>

        <div class="input-group">
            <label for="targetId">Phone Number</label>
            <input type="text" id="targetId" class="phone-input" placeholder="Enter phone number">
        </div>

        <div class="btn-group">
            <button id="callButton" class="btn btn-call" onclick="makeCall()">
                <i class="fas fa-phone-alt"></i> Call
            </button>
            <button id="hangupButton" class="btn btn-hangup" onclick="hangup()">
                <i class="fas fa-phone-slash"></i> Hang Up
            </button>
        </div>

        <audio id="remoteAudio" autoplay></audio>
    </div>

    <script>
        let userAgent;
        let session;
        let sipUser = "50010"; // Default to Bangla

        function changeLanguage() {
            const languageSelect = document.getElementById("languageSelect");
            sipUser = languageSelect.value === "bangla" ? "50010" : "50010";
            console.log(`Language changed to ${languageSelect.value}, SIP User: ${sipUser}`);
        }

        function register() {
            const server = "wss://westernaiws.speaklar.com:8089/ws";
            const sipPassword = "ln47UEwtfyNHyzd";
            const sipDomain = "103.101.110.120";

            if (!sipUser) {
                alert("⚠️ SIP Username is missing!");
                return;
            }

            userAgent = new SIP.UA({
                uri: `sip:${sipUser}@${sipDomain}`,
                transportOptions: { wsServers: server },
                authorizationUser: sipUser,
                password: sipPassword,
                sessionDescriptionHandlerFactoryOptions: {
                    constraints: { audio: true, video: false },
                    peerConnectionConfiguration: {
                        iceServers: [{ urls: "stun:stun.l.google.com:19302" }]
                    }
                }
            });

            userAgent.on('registered', () => {
                console.log("✅ SIP Registered!");
                makeCall(); // Initiate call after successful registration
            });

            userAgent.on('registrationFailed', (error) => {
                console.error("❌ Registration failed:", error);
                document.getElementById("callButton").style.display = "inline";
                alert("Registration failed. Please try again.");
            });

            userAgent.on('invite', (incomingSession) => {
                console.log("📞 Incoming call...");
                session = incomingSession;
                session.accept();
                session.sessionDescriptionHandler.peerConnection.getReceivers().forEach(receiver => {
                    if (receiver.track.kind === "audio") {
                        document.getElementById("remoteAudio").srcObject = new MediaStream([receiver.track]);
                    }
                });
            });
        }

        function makeCall() {
            const callButton = document.getElementById("callButton");
            const hangupButton = document.getElementById("hangupButton");
            callButton.style.display = "none";
            callButton.disabled = true;

            const target = document.getElementById("targetId").value.trim();

            if (!target) {
                alert("⚠️ Please enter a phone number before making a call!");
                callButton.style.display = "inline";
                callButton.disabled = false;
                return;
            }

            if (!userAgent) {
                register(); // Register first if not registered
                return;
            }

            session = userAgent.invite(`sip:${target}@103.177.125.134`);

            session.on('accepted', () => {
                console.log("✅ Call connected!");
                let remoteAudio = document.getElementById("remoteAudio");
                let remoteStream = new MediaStream();

                session.sessionDescriptionHandler.peerConnection.getReceivers().forEach(receiver => {
                    if (receiver.track.kind === "audio") {
                        console.log("🔊 Received audio track:", receiver.track);
                        remoteStream.addTrack(receiver.track);
                    }
                });

                remoteAudio.srcObject = remoteStream;
                hangupButton.style.display = "inline";
            });

            session.on('terminated', () => {
                console.log("📴 Call ended!");
                hangupButton.style.display = "none";
                callButton.style.display = "inline";
                callButton.disabled = false;
            });

            session.on('failed', () => {
                console.log("❌ Call failed!");
                hangupButton.style.display = "none";
                callButton.style.display = "inline";
                callButton.disabled = false;
                alert("Call failed. Please try again.");
            });
        }

        function hangup() {
            if (session) {
                session.bye();
                console.log("📴 Call ended.");
                document.getElementById("hangupButton").style.display = "none";
                document.getElementById("callButton").style.display = "inline";
                unregister(); // Unregister the SIP client
            }
        }

        function unregister() {
            if (userAgent) {
                userAgent.unregister();
                console.log("🚪 SIP Unregistered!");
                userAgent = null;
            }
        }
    </script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</body>
</html>
