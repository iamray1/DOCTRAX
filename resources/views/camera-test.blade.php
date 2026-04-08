<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Camera Test</title>
    <style>
        :root {
            --primary: #0056b3;
            --primary-dark: #004494;
            --bg: #f3f6fb;
            --card: #ffffff;
            --text: #1b263b;
            --muted: #64748b;
            --border: #dbe3ee;
            --danger: #dc2626;
            --ok: #15803d;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Poppins, Arial, sans-serif;
            background: linear-gradient(180deg, #eef4fb 0%, #f8fafc 100%);
            color: var(--text);
            min-height: 100vh;
            padding: 32px 16px;
        }
        .wrap {
            max-width: 860px;
            margin: 0 auto;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
        }
        h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }
        p {
            margin: 0 0 12px;
            color: var(--muted);
            line-height: 1.6;
        }
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin: 18px 0;
        }
        button {
            border: none;
            border-radius: 10px;
            padding: 12px 18px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-ghost { background: #e8eef7; color: var(--text); }
        .status {
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #f8fafc;
            padding: 14px 16px;
            margin: 14px 0 18px;
            white-space: pre-wrap;
            word-break: break-word;
            font-size: 14px;
        }
        .status.ok { border-color: #bbf7d0; background: #f0fdf4; color: var(--ok); }
        .status.err { border-color: #fecaca; background: #fef2f2; color: var(--danger); }
        .video-box {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: #0f172a;
            min-height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        video {
            width: 100%;
            display: block;
            background: #000;
        }
        .meta {
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid var(--border);
            font-size: 13px;
            color: var(--text);
            white-space: pre-wrap;
        }
        .perm-box { margin: 14px 0 18px; padding: 16px 18px; border-radius: 12px; font-size: 13.5px; line-height: 1.6; }
        .perm-denied { background: #fef2f2; border: 1px solid #fecaca; }
        .perm-ok { background: #f0fdf4; border: 1px solid #bbf7d0; }
        .perm-info { background: #eff6ff; border: 1px solid #bfdbfe; }
        @media (max-width: 700px) {
            body { padding: 18px 12px; }
            .card { padding: 18px; }
            .video-box { min-height: 220px; }
            h1 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1>Camera Test</h1>
            <p>This page bypasses the QR scanner logic and tests only the browser and OS camera path.</p>
            <p>If the video appears here, the webcam works in the browser and the remaining issue is in QR scanning only. If video does not appear here, the problem is browser permission, Windows privacy, or another app using the webcam.</p>

            <div id="permCheckBox" style="display:none"></div>

            <div class="actions">
                <button class="btn-primary" id="startBtn" type="button">Start Camera Test</button>
                <button class="btn-ghost" id="stopBtn" type="button">Stop Camera</button>
            </div>

            <div class="status" id="statusBox">Idle.</div>

            <div class="video-box" id="videoBox">
                <video id="cameraVideo" autoplay muted playsinline></video>
            </div>

            <div class="meta" id="metaBox">No device information yet.</div>
        </div>
    </div>

    <script>
    (function () {
        var video = document.getElementById('cameraVideo');
        var statusBox = document.getElementById('statusBox');
        var metaBox = document.getElementById('metaBox');
        var activeStream = null;

        function setStatus(message, tone) {
            statusBox.textContent = message;
            statusBox.className = 'status' + (tone ? ' ' + tone : '');
        }

        // Check permission state immediately on page load
        (function checkPermission() {
            var box = document.getElementById('permCheckBox');
            if (!navigator.permissions) {
                box.style.display = 'none';
                return;
            }
            navigator.permissions.query({ name: 'camera' }).then(function (result) {
                if (result.state === 'denied') {
                    box.innerHTML = '<strong style="color:#b91c1c;font-size:15px;">&#9888; Your browser has BLOCKED camera for this site.</strong>'
                        + '<p style="margin:10px 0 0;color:#7f1d1d;">The browser permission is set to <strong>Block</strong> — this overrides Windows privacy settings.</p>'
                        + '<p style="margin:8px 0 0;color:#7f1d1d;"><strong>Fix (takes 10 seconds):</strong></p>'
                        + '<ol style="margin:6px 0 0;padding-left:20px;color:#7f1d1d;line-height:2;">'
                        + '<li>Look at the <strong>address bar</strong> above — click the <strong>&#128274; lock icon</strong> or <strong>camera icon</strong>.</li>'
                        + '<li>Click <strong>Site settings</strong> (Chrome) or <strong>Permissions for this site</strong> (Edge).</li>'
                        + '<li>Find <strong>Camera</strong> → change it from <strong>Block → Allow</strong>.</li>'
                        + '<li><strong>Reload this page</strong>, then click Start Camera Test.</li>'
                        + '</ol>'
                        + '<p style="margin:10px 0 0;color:#7f1d1d;"><strong>Or paste in the address bar:</strong> <code style="background:#fff3f3;padding:2px 6px;border-radius:4px;font-size:13px;">chrome://settings/content/camera</code> — find <strong>' + location.origin + '</strong> in the blocked list and remove it.</p>';
                    box.className = 'perm-box perm-denied';
                } else if (result.state === 'granted') {
                    box.innerHTML = '<strong style="color:#15803d;">&#10003; Browser permission: GRANTED</strong> — camera should work. Click Start Camera Test.';
                    box.className = 'perm-box perm-ok';
                } else {
                    box.innerHTML = '<strong style="color:#1d4ed8;">&#8505; Browser permission: not yet decided (will prompt when you click Start).</strong>';
                    box.className = 'perm-box perm-info';
                }
                box.style.display = 'block';
                result.onchange = function () { checkPermission(); };
            }).catch(function () { box.style.display = 'none'; });
        })();

        function setMeta(data) {
            metaBox.textContent = data;
        }

        function stopCamera() {
            if (activeStream) {
                activeStream.getTracks().forEach(function (track) { track.stop(); });
                activeStream = null;
            }
            video.srcObject = null;
            setStatus('Camera stopped.', '');
        }

        function normalizeError(err) {
            var name = err && err.name ? err.name : 'UnknownError';
            var message = err && err.message ? err.message : String(err || 'Unknown error');
            return name + ': ' + message;
        }

        document.getElementById('stopBtn').addEventListener('click', stopCamera);

        document.getElementById('startBtn').addEventListener('click', function () {
            stopCamera();

            if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                setStatus('Blocked: camera requires HTTPS or localhost.', 'err');
                return;
            }
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                setStatus('Blocked: this browser does not support getUserMedia.', 'err');
                return;
            }

            setStatus('Requesting camera access...', '');

            var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            var constraints = isMobile
                ? { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } }
                : { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } };

            navigator.mediaDevices.getUserMedia({ video: constraints, audio: false })
                .then(function (stream) {
                    activeStream = stream;
                    video.srcObject = stream;
                    return video.play().catch(function () { return null; }).then(function () {
                        var track = stream.getVideoTracks && stream.getVideoTracks()[0] ? stream.getVideoTracks()[0] : null;
                        var settings = track && track.getSettings ? track.getSettings() : {};
                        var details = [
                            'Camera test passed.',
                            'Label: ' + ((track && track.label) ? track.label : 'Unknown'),
                            'Device ID: ' + (settings.deviceId || 'Unavailable'),
                            'Resolution: ' + ((settings.width || '?') + ' x ' + (settings.height || '?')),
                            'Facing Mode: ' + (settings.facingMode || 'Unavailable'),
                            'Secure Context: ' + String(window.isSecureContext),
                            'Origin: ' + location.origin,
                            'User Agent: ' + navigator.userAgent
                        ].join('\n');
                        setMeta(details);
                        setStatus('Camera live. If you can see yourself or the webcam feed here, browser camera access is working.', 'ok');
                    });
                })
                .catch(function (err) {
                    var errStr = normalizeError(err);
                    setMeta([
                        'Camera test failed.',
                        'Error: ' + errStr,
                        'Secure Context: ' + String(window.isSecureContext),
                        'Origin: ' + location.origin,
                        'User Agent: ' + navigator.userAgent
                    ].join('\n'));

                    var fixHtml = '';
                    if (err && (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError')) {
                        fixHtml = '<div style="margin-top:18px;padding:16px 18px;border-radius:12px;background:#fef2f2;border:1px solid #fecaca;font-size:13.5px;line-height:1.7;">'
                            + '<strong style="color:#b91c1c;font-size:14px;">Permission Denied — most likely cause: Windows desktop app toggle is OFF</strong>'
                            + '<ol style="margin:12px 0 0;padding-left:22px;color:#7f1d1d;">'
                            + '<li>Press <strong>Win + I</strong> to open Windows Settings.</li>'
                            + '<li>Go to <strong>Privacy &amp; security → Camera</strong>.</li>'
                            + '<li>Make sure <strong>"Allow apps to access your camera"</strong> is <strong>ON</strong>.</li>'
                            + '<li>Scroll down and make sure <strong>"Allow desktop apps to access your camera"</strong> is also <strong>ON</strong>. This is a separate toggle that is often missed.</li>'
                            + '<li>After enabling it, come back and click <strong>Start Camera Test</strong> again.</li>'
                            + '</ol>'
                            + '<hr style="border:none;border-top:1px solid #fecaca;margin:14px 0;">'
                            + '<strong style="color:#b91c1c;">If Windows settings are already ON:</strong>'
                            + '<ol style="margin:8px 0 0;padding-left:22px;color:#7f1d1d;">'
                            + '<li>In Chrome/Edge, click the <strong>lock icon</strong> (or camera icon) in the address bar.</li>'
                            + '<li>Click <strong>Site settings</strong> or <strong>Permissions</strong>.</li>'
                            + '<li>Set <strong>Camera</strong> to <strong>Allow</strong> (not Ask, not Block).</li>'
                            + '<li>Reload this page and try again.</li>'
                            + '</ol>'
                            + '<hr style="border:none;border-top:1px solid #fecaca;margin:14px 0;">'
                            + '<strong style="color:#b91c1c;">Nuclear reset (Chrome):</strong><br>'
                            + '<span style="color:#7f1d1d;">Paste this in the address bar → <code style="background:#fff;padding:2px 5px;border-radius:4px;">chrome://settings/content/camera</code> → find and remove <strong>' + location.origin + '</strong> from the Blocked list, then reload.</span>'
                            + '</div>';
                    } else if (err && err.name === 'NotReadableError') {
                        fixHtml = '<div style="margin-top:18px;padding:16px 18px;border-radius:12px;background:#fffbeb;border:1px solid #fde68a;font-size:13.5px;line-height:1.7;">'
                            + '<strong style="color:#92400e;">Webcam is busy — another app is using it</strong>'
                            + '<p style="margin:8px 0 0;color:#78350f;">Close Zoom, Teams, Skype, OBS, or the built-in Windows Camera app, then click Start Camera Test again.</p>'
                            + '</div>';
                    }

                    if (fixHtml) {
                        var fixEl = document.getElementById('fixInstructions');
                        if (!fixEl) {
                            fixEl = document.createElement('div');
                            fixEl.id = 'fixInstructions';
                            document.querySelector('.card').appendChild(fixEl);
                        }
                        fixEl.innerHTML = fixHtml;
                    }

                    setStatus('Camera test failed: ' + errStr, 'err');
                });
        });
    })();
    </script>
</body>
</html>
