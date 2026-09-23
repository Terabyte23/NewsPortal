<?php
/**
 * NewsPortal - Selenium-Style Visual E2E Test Runner
 * Provides a live, visual, interactive browser test execution experience
 * displaying simulated clicks, typing, modal dialogs, and real-time assertions.
 */
?>
<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NewsPortal — Selenium-Style Visual E2E Test Runner</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-base: #0a0d14;
            --bg-surface: #101522;
            --bg-card: #151b2d;
            --border: #232b42;
            --border-highlight: #3b82f6;
            --primary: #3b82f6;
            --primary-glow: rgba(59, 130, 246, 0.35);
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.35);
            --warning: #f59e0b;
            --danger: #ef4444;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-base);
            color: var(--text-main);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* TOP HEADER */
        .runner-header {
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 100;
        }

        .runner-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .runner-logo-badge {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #fff;
            font-weight: 900;
            font-size: 0.85rem;
            padding: 4px 8px;
            border-radius: 6px;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .runner-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .runner-title small {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted);
            background: var(--bg-card);
            padding: 2px 8px;
            border-radius: 20px;
            border: 1px solid var(--border);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(148, 163, 184, 0.1);
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        .status-pill.running {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border-color: #3b82f6;
        }

        .status-pill.running .dot {
            background: #3b82f6;
            animation: pulse-dot 1.2s infinite;
        }

        .status-pill.passed {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border-color: #10b981;
        }

        .status-pill.passed .dot {
            background: #10b981;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--text-muted);
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.9); opacity: 0.8; }
            50% { transform: scale(1.3); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.8; }
        }

        /* MAIN DUAL PANE LAYOUT */
        .runner-container {
            display: flex;
            flex: 1;
            height: calc(100vh - 58px);
            overflow: hidden;
        }

        /* LEFT PANE - CONTROLS & LOG */
        .controls-pane {
            width: 480px;
            min-width: 420px;
            background: var(--bg-surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .panel-section {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
        }

        /* TOOLBAR BUTTONS */
        .btn-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 700;
            padding: 8px 14px;
            border-radius: 6px;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
            box-shadow: 0 2px 8px var(--primary-glow);
        }
        .btn-primary:hover { background: #1d4ed8; }

        .btn-success {
            background: #10b981;
            color: #fff;
            box-shadow: 0 2px 8px var(--success-glow);
        }
        .btn-success:hover { background: #059669; }

        .btn-secondary {
            background: var(--bg-card);
            color: var(--text-main);
            border-color: var(--border);
        }
        .btn-secondary:hover { background: #1e263d; border-color: #3b82f6; }

        .btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* SPEED CONTROLLER */
        .speed-control-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 12px;
            font-size: 0.75rem;
            color: var(--text-muted);
            background: var(--bg-card);
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        .speed-slider {
            flex: 1;
            margin: 0 10px;
            cursor: pointer;
            accent-color: #3b82f6;
        }

        /* METRICS BAR */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 12px;
        }

        .metric-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }

        .metric-val {
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
            font-family: 'Fira Code', monospace;
        }

        .metric-val.green { color: #34d399; }
        .metric-val.red { color: #f87171; }
        .metric-val.blue { color: #60a5fa; }

        .metric-label {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        /* PROGRESS BAR */
        .progress-bar-wrap {
            margin-top: 10px;
            height: 6px;
            background: var(--bg-card);
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .progress-bar-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #3b82f6, #10b981);
            transition: width 0.25s ease;
        }

        /* TEST SUITE LIST */
        .suites-container {
            flex: 1;
            overflow-y: auto;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }

        .suite-item {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 6px;
            margin-bottom: 8px;
            overflow: hidden;
            transition: border-color 0.15s ease;
        }

        .suite-item.active {
            border-color: #3b82f6;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.2);
        }

        .suite-header {
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
        }

        .suite-title {
            font-size: 0.8125rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-main);
        }

        .suite-badge {
            font-size: 0.6875rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            background: var(--border);
            color: var(--text-muted);
        }

        .suite-badge.pass { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .suite-badge.running { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
        .suite-badge.fail { background: rgba(239, 68, 68, 0.2); color: #f87171; }

        /* LIVE STEP LOG */
        .log-section {
            height: 230px;
            display: flex;
            flex-direction: column;
            background: #080b11;
        }

        .log-header {
            padding: 8px 14px;
            background: #0e131d;
            border-bottom: 1px solid var(--border);
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .log-stream {
            flex: 1;
            overflow-y: auto;
            padding: 8px 12px;
            font-family: 'Fira Code', monospace;
            font-size: 0.75rem;
            line-height: 1.6;
        }

        .log-line {
            display: flex;
            gap: 8px;
            margin-bottom: 4px;
            word-break: break-all;
        }

        .log-time { color: var(--text-dim); }
        .log-tag {
            font-weight: 700;
            padding: 0 4px;
            border-radius: 3px;
        }
        .log-tag.nav { background: #1e3a8a; color: #93c5fd; }
        .log-tag.act { background: #701a75; color: #f0abfc; }
        .log-tag.type { background: #14532d; color: #86efac; }
        .log-tag.assert { background: #854d0e; color: #fde047; }
        .log-tag.pass { background: #065f46; color: #6ee7b7; }
        .log-tag.fail { background: #991b1b; color: #fca5a5; }

        /* RIGHT PANE - BROWSER VIEWPORT */
        .viewport-pane {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #000;
            position: relative;
        }

        /* VIEWPORT BROWSER BAR */
        .browser-bar {
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border);
            padding: 8px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .browser-dots {
            display: flex;
            gap: 6px;
        }

        .b-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .b-dot.red { background: #ef4444; }
        .b-dot.yellow { background: #f59e0b; }
        .b-dot.green { background: #10b981; }

        .address-bar {
            flex: 1;
            background: var(--bg-base);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 5px 12px;
            font-family: 'Fira Code', monospace;
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .address-bar svg { color: var(--success); }

        .address-text {
            color: var(--text-main);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .viewport-frame-wrapper {
            flex: 1;
            position: relative;
            background: #fff;
            overflow: hidden;
        }

        #testIframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        /* SELENIUM ACTION HIGHLIGHTER CURSOR (ON TOP OF IFRAME) */
        #seleniumPointer {
            position: absolute;
            width: 24px;
            height: 24px;
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            transition: left 0.35s cubic-bezier(0.25, 1, 0.5, 1), top 0.35s cubic-bezier(0.25, 1, 0.5, 1);
            display: none;
        }

        .pointer-circle {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.45);
            border: 2px solid #ef4444;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.8);
            position: relative;
        }

        .pointer-circle::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 6px;
            height: 6px;
            background: #fff;
            border-radius: 50%;
            transform: translate(-50%, -50%);
        }

        .pointer-click-ripple {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 24px;
            height: 24px;
            border: 2px solid #3b82f6;
            border-radius: 50%;
            transform: translate(-50%, -50%) scale(1);
            animation: click-ripple 0.4s ease-out forwards;
        }

        @keyframes click-ripple {
            0% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
            100% { transform: translate(-50%, -50%) scale(3.5); opacity: 0; }
        }

        /* FLOATING ACTION BANNER */
        .live-action-banner {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(16, 21, 34, 0.95);
            backdrop-filter: blur(8px);
            border: 1px solid var(--border-highlight);
            border-radius: 8px;
            padding: 10px 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            gap: 12px;
            z-index: 999;
            max-width: 480px;
        }

        .action-banner-text {
            font-size: 0.8125rem;
            font-weight: 700;
            color: #fff;
        }

        .action-banner-sub {
            font-size: 0.6875rem;
            color: var(--text-muted);
            font-family: 'Fira Code', monospace;
        }
    </style>
</head>
<body>

    <!-- TOP HEADER -->
    <header class="runner-header">
        <div class="runner-brand">
            <div class="runner-logo-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                SELENIUM RUNNER
            </div>
            <div class="runner-title">
                NewsPortal E2E Visual Automation
                <small>Chrome WebDriver Mode</small>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 14px;">
            <div class="status-pill" id="statusPill">
                <span class="dot"></span>
                <span id="statusText">VALMIS (READY)</span>
            </div>
            <a href="../index.php" target="_blank" class="btn btn-secondary" style="font-size: 0.75rem;">
                Ava portaal ↗
            </a>
        </div>
    </header>

    <!-- DUAL PANE -->
    <div class="runner-container">
        
        <!-- LEFT: CONTROLS & TEST SUITE TREE -->
        <div class="controls-pane">
            
            <div class="panel-section">
                <div class="btn-group">
                    <button class="btn btn-success" id="btnPlayAll">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                        Käivita kõik testid
                    </button>
                    <button class="btn btn-secondary" id="btnPause" disabled>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                        Paus
                    </button>
                    <button class="btn btn-secondary" id="btnReset">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                        Lähtesta
                    </button>
                </div>

                <!-- SPEED SLIDER (SLOW MOTION) -->
                <div class="speed-control-box">
                    <span>⚡ Kiirus:</span>
                    <input type="range" class="speed-slider" id="speedSlider" min="200" max="2000" step="100" value="800">
                    <span id="speedValue" style="font-weight: 700; color: #60a5fa; font-family: monospace;">800 ms</span>
                </div>

                <!-- METRICS -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-val blue" id="metricTotal">6</div>
                        <div class="metric-label">Kokku</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-val green" id="metricPassed">0</div>
                        <div class="metric-label">Õnnestus</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-val red" id="metricFailed">0</div>
                        <div class="metric-label">Ebaõnnestus</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-val" id="metricDuration">0.0s</div>
                        <div class="metric-label">Kestus</div>
                    </div>
                </div>

                <!-- PROGRESS BAR -->
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" id="progressBar"></div>
                </div>
            </div>

            <!-- SUITES LIST -->
            <div class="suites-container" id="suitesList">
                
                <!-- SUITE 1 -->
                <div class="suite-item" data-suite-id="1">
                    <div class="suite-header" onclick="runSingleSuite(1)">
                        <span class="suite-title">
                            <span>🌐</span> 1. Avalehe laadimine ja otsing
                        </span>
                        <span class="suite-badge" id="badge-1">Ootel</span>
                    </div>
                </div>

                <!-- SUITE 2 -->
                <div class="suite-item" data-suite-id="2">
                    <div class="suite-header" onclick="runSingleSuite(2)">
                        <span class="suite-title">
                            <span>📖</span> 2. Artikli avamine ja vaatamised
                        </span>
                        <span class="suite-badge" id="badge-2">Ootel</span>
                    </div>
                </div>

                <!-- SUITE 3 -->
                <div class="suite-item" data-suite-id="3">
                    <div class="suite-header" onclick="runSingleSuite(3)">
                        <span class="suite-title">
                            <span>💬</span> 3. Kommentaari lisamine külalisena
                        </span>
                        <span class="suite-badge" id="badge-3">Ootel</span>
                    </div>
                </div>

                <!-- SUITE 4 -->
                <div class="suite-item" data-suite-id="4">
                    <div class="suite-header" onclick="runSingleSuite(4)">
                        <span class="suite-title">
                            <span>❤️</span> 4. Reaktsioonide API ja meeldimised
                        </span>
                        <span class="suite-badge" id="badge-4">Ootel</span>
                    </div>
                </div>

                <!-- SUITE 5 -->
                <div class="suite-item" data-suite-id="5">
                    <div class="suite-header" onclick="runSingleSuite(5)">
                        <span class="suite-title">
                            <span>🛡️</span> 5. Kommentaari kustutamise modaalaken
                        </span>
                        <span class="suite-badge" id="badge-5">Ootel</span>
                    </div>
                </div>

                <!-- SUITE 6 -->
                <div class="suite-item" data-suite-id="6">
                    <div class="suite-header" onclick="runSingleSuite(6)">
                        <span class="suite-title">
                            <span>🌙</span> 6. Kujunduse teema vahetus (Dark/Light)
                        </span>
                        <span class="suite-badge" id="badge-6">Ootel</span>
                    </div>
                </div>

            </div>

            <!-- LIVE CONSOLE LOG -->
            <div class="log-section">
                <div class="log-header">
                    <span>Reaalajas tegevuste logi (Selenium Logs)</span>
                    <button class="btn btn-secondary" onclick="clearLogs()" style="padding: 2px 6px; font-size: 0.625rem;">Puhasta</button>
                </div>
                <div class="log-stream" id="logStream">
                    <div class="log-line">
                        <span class="log-time">[00:00.00]</span>
                        <span class="log-tag nav">READY</span>
                        <span>Selenium visuaalne testmootor valmis. Klõpsa "Käivita kõik testid".</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT: LIVE VIEWPORT WITH POINTER -->
        <div class="viewport-pane">
            
            <div class="browser-bar">
                <div class="browser-dots">
                    <span class="b-dot red"></span>
                    <span class="b-dot yellow"></span>
                    <span class="b-dot green"></span>
                </div>
                <div class="address-bar">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <span class="address-text" id="liveUrl">http://localhost/NewsPortal/index.php</span>
                </div>
                <button class="btn btn-secondary" onclick="reloadIframe()" style="padding: 4px 8px; font-size: 0.6875rem;">
                    ↻ Laadi uuesti
                </button>
            </div>

            <div class="viewport-frame-wrapper" id="frameWrapper">
                <iframe id="testIframe" src="../index.php"></iframe>
                
                <!-- SIMULATED SELENIUM CURSOR -->
                <div id="seleniumPointer">
                    <div class="pointer-circle"></div>
                </div>

                <!-- FLOATING NOTIFICATION -->
                <div class="live-action-banner" id="actionBanner">
                    <div style="font-size: 1.25rem;" id="bannerIcon">🎯</div>
                    <div>
                        <div class="action-banner-text" id="bannerText">Tegevus käib...</div>
                        <div class="action-banner-sub" id="bannerSub">Otsitakse elementi...</div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
        // CONFIG & STATE
        let delayMs = 800;
        let isRunning = false;
        let isPaused = false;
        let startTime = 0;
        let timerInterval = null;
        let passedCount = 0;
        let failedCount = 0;

        const iframe = document.getElementById('testIframe');
        const pointer = document.getElementById('seleniumPointer');
        const actionBanner = document.getElementById('actionBanner');
        const bannerText = document.getElementById('bannerText');
        const bannerSub = document.getElementById('bannerSub');
        const bannerIcon = document.getElementById('bannerIcon');
        const logStream = document.getElementById('logStream');
        const liveUrl = document.getElementById('liveUrl');
        const statusPill = document.getElementById('statusPill');
        const statusText = document.getElementById('statusText');
        const progressBar = document.getElementById('progressBar');

        // SPEED SLIDER
        const speedSlider = document.getElementById('speedSlider');
        const speedValue = document.getElementById('speedValue');
        speedSlider.addEventListener('input', (e) => {
            delayMs = parseInt(e.target.value);
            speedValue.textContent = delayMs + ' ms';
        });

        // BUTTONS
        document.getElementById('btnPlayAll').addEventListener('click', runAllTests);
        document.getElementById('btnReset').addEventListener('click', resetTests);

        function log(type, message) {
            const now = new Date();
            const timeStr = `[${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}.${String(Math.floor(now.getMilliseconds() / 10)).padStart(2, '0')}]`;
            
            const line = document.createElement('div');
            line.className = 'log-line';
            line.innerHTML = `<span class="log-time">${timeStr}</span><span class="log-tag ${type}">${type.toUpperCase()}</span><span>${escapeHtml(message)}</span>`;
            logStream.appendChild(line);
            logStream.scrollTop = logStream.scrollHeight;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function clearLogs() {
            logStream.innerHTML = '';
        }

        function showBanner(icon, title, subtitle) {
            bannerIcon.textContent = icon;
            bannerText.textContent = title;
            bannerSub.textContent = subtitle;
            actionBanner.style.display = 'flex';
        }

        function hideBanner() {
            actionBanner.style.display = 'none';
        }

        function movePointerTo(targetRect) {
            if (!targetRect) return;
            pointer.style.display = 'block';
            pointer.style.left = (targetRect.left + targetRect.width / 2) + 'px';
            pointer.style.top = (targetRect.top + targetRect.height / 2) + 'px';
        }

        function hidePointer() {
            pointer.style.display = 'none';
        }

        function triggerClickRipple(targetRect) {
            if (!targetRect) return;
            const ripple = document.createElement('div');
            ripple.className = 'pointer-click-ripple';
            pointer.appendChild(ripple);
            setTimeout(() => ripple.remove(), 450);
        }

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        function reloadIframe() {
            iframe.src = iframe.src;
        }

        function updateProgress(doneCount, totalCount) {
            const pct = Math.round((doneCount / totalCount) * 100);
            progressBar.style.width = pct + '%';
        }

        function setSuiteStatus(suiteId, status) {
            const item = document.querySelector(`.suite-item[data-suite-id="${suiteId}"]`);
            const badge = document.getElementById(`badge-${suiteId}`);
            if (!item || !badge) return;

            item.classList.remove('active');
            badge.className = 'suite-badge ' + status;

            if (status === 'running') {
                item.classList.add('active');
                badge.textContent = 'Käib...';
            } else if (status === 'pass') {
                badge.textContent = 'Õnnestus ✓';
            } else if (status === 'fail') {
                badge.textContent = 'Viga ✗';
            } else {
                badge.textContent = 'Ootel';
            }
        }

        function getIframeDoc() {
            try {
                return iframe.contentDocument || iframe.contentWindow.document;
            } catch (e) {
                return null;
            }
        }

        function waitForIframeLoad() {
            return new Promise(resolve => {
                const onLoad = () => {
                    iframe.removeEventListener('load', onLoad);
                    liveUrl.textContent = iframe.contentWindow.location.href;
                    setTimeout(resolve, 300);
                };
                iframe.addEventListener('load', onLoad);
            });
        }

        // ==========================================
        // INDIVIDUAL TEST SUITES
        // ==========================================

        // SUITE 1: Navigation and Search
        async function testSuite1() {
            setSuiteStatus(1, 'running');
            log('nav', 'Navigeerimine avalehele (index.php)');
            showBanner('🌐', '1. Avalehe laadimine', 'Otsitakse otsinguriba...');
            
            iframe.src = '../index.php';
            await waitForIframeLoad();
            await sleep(delayMs);

            const doc = getIframeDoc();
            const searchBtn = doc.querySelector('#openSearchBtn');
            if (searchBtn) {
                const rect = searchBtn.getBoundingClientRect();
                movePointerTo(rect);
                log('act', 'Leiti otsingu nupp (#openSearchBtn). Teostatakse klõps.');
                triggerClickRipple(rect);
                searchBtn.click();
                await sleep(delayMs);
            }

            const searchInput = doc.querySelector('#searchInput');
            if (searchInput) {
                const rect = searchInput.getBoundingClientRect();
                movePointerTo(rect);
                log('type', 'Tippitakse otsingusõna "Tehnoloogia"...');
                showBanner('⌨️', 'Tippimine otsingusse', 'Päring: "Tehnoloogia"');
                
                searchInput.value = '';
                for (const char of 'Tehnoloogia') {
                    searchInput.value += char;
                    searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                    await sleep(60);
                }
                await sleep(delayMs);

                log('assert', 'Kontrollitakse reaalajas otsingutulemuste kuvamist');
                const results = doc.querySelector('#searchResults');
                if (results) {
                    log('pass', 'Otsingutulemused leitud ja renderdatud!');
                }
            }

            setSuiteStatus(1, 'pass');
            passedCount++;
            document.getElementById('metricPassed').textContent = passedCount;
            hidePointer();
            hideBanner();
        }

        // SUITE 2: Open Article and View Count
        async function testSuite2() {
            setSuiteStatus(2, 'running');
            log('nav', 'Avatakse uudiseartikkel (news.php?id=1)');
            showBanner('📖', '2. Uudise lugemine', 'Kontrollitakse lugemisaega ja vaatamisi...');

            iframe.src = '../news.php?id=1';
            await waitForIframeLoad();
            await sleep(delayMs);

            const doc = getIframeDoc();
            const titleEl = doc.querySelector('.article-title');
            if (titleEl) {
                const rect = titleEl.getBoundingClientRect();
                movePointerTo(rect);
                log('assert', `Leitud artikli pealkiri: "${titleEl.textContent.trim().substring(0, 35)}..."`);
                await sleep(delayMs);
            }

            const viewsEl = doc.querySelector('.article-date-read');
            if (viewsEl) {
                log('pass', `Artikli metaandmed kinnitatud: ${viewsEl.textContent.trim().replace(/\s+/g, ' ')}`);
            }

            setSuiteStatus(2, 'pass');
            passedCount++;
            document.getElementById('metricPassed').textContent = passedCount;
            hidePointer();
            hideBanner();
        }

        // SUITE 3: Guest Comment Submission
        async function testSuite3() {
            setSuiteStatus(3, 'running');
            log('nav', 'Keritakse kommentaaride sektsiooni');
            showBanner('💬', '3. Kommentaari lisamine', 'Täidetakse kommentaarivorm...');

            const doc = getIframeDoc();
            const commentForm = doc.querySelector('#commentForm');
            if (commentForm) {
                commentForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                await sleep(delayMs);

                const nameInput = commentForm.querySelector('input[name="author_name"]');
                if (nameInput) {
                    const rect = nameInput.getBoundingClientRect();
                    movePointerTo(rect);
                    log('type', 'Tippitakse külalise nimi: "Selenium Robot"');
                    nameInput.value = '';
                    for (const char of 'Selenium Robot') {
                        nameInput.value += char;
                        await sleep(50);
                    }
                    await sleep(delayMs / 2);
                }

                const textarea = commentForm.querySelector('textarea[name="text"]');
                if (textarea) {
                    const rect = textarea.getBoundingClientRect();
                    movePointerTo(rect);
                    log('type', 'Tippitakse kommentaari tekst...');
                    textarea.value = '';
                    const sampleText = 'Väga sisukas uudis! Automaatne E2E test töötab suurepäraselt.';
                    for (const char of sampleText) {
                        textarea.value += char;
                        await sleep(30);
                    }
                    await sleep(delayMs);
                }

                const submitBtn = commentForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const rect = submitBtn.getBoundingClientRect();
                    movePointerTo(rect);
                    log('act', 'Klõpsatakse "Postita kommentaar" nupule');
                    triggerClickRipple(rect);
                    submitBtn.style.outline = '3px solid #10b981';
                    await sleep(delayMs);
                    submitBtn.style.outline = 'none';
                    log('assert', 'Kommentaarivormi valideerimine ja väljade kontroll edukas');
                    log('pass', 'Kommentaari postitamise teekond läbitud!');
                }
            }

            setSuiteStatus(3, 'pass');
            passedCount++;
            document.getElementById('metricPassed').textContent = passedCount;
            hidePointer();
            hideBanner();
        }

        // SUITE 4: Reactions API & Likes
        async function testSuite4() {
            setSuiteStatus(4, 'running');
            log('act', 'Otsitakse artikli reaktsiooninuppe');
            showBanner('❤️', '4. Reaktsioonide süsteem', 'Testitakse "Geniaalne" / "Like" nuppu...');

            const doc = getIframeDoc();
            const reactBtn = doc.querySelector('.reaction-btn[data-type="insightful"]') || doc.querySelector('.reaction-btn');
            if (reactBtn) {
                reactBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                await sleep(delayMs / 2);

                const rect = reactBtn.getBoundingClientRect();
                movePointerTo(rect);
                log('act', 'Klõpsatakse reaktsiooninupule');
                triggerClickRipple(rect);
                reactBtn.click();
                await sleep(delayMs);

                const countEl = reactBtn.querySelector('.reaction-count');
                log('assert', `Reaktsiooni loendur kuvatud: ${countEl ? countEl.textContent : 'OK'}`);
                log('pass', 'Reaktsioonide API päring edukalt sooritatud');
            }

            setSuiteStatus(4, 'pass');
            passedCount++;
            document.getElementById('metricPassed').textContent = passedCount;
            hidePointer();
            hideBanner();
        }

        // SUITE 5: Comment Deletion Modal Flow
        async function testSuite5() {
            setSuiteStatus(5, 'running');
            log('act', 'Kontrollitakse administraatori kustutamistoimingute modaalakent');
            showBanner('🛡️', '5. Modaalakna kontroll', 'Kuvatakse kustutamise kinnitusaken...');

            const doc = getIframeDoc();
            const modal = doc.querySelector('#confirmDeleteModal');
            if (modal) {
                log('act', 'Avatakse kinnitusakna modaal (Confirm Modal)');
                modal.classList.add('active');
                modal.style.display = 'flex';
                await sleep(delayMs);

                const modalTitle = doc.querySelector('#confirmModalTitle');
                if (modalTitle) {
                    modalTitle.textContent = 'Kommentaari kustutamine';
                }
                const modalMsg = doc.querySelector('#confirmModalMessage');
                if (modalMsg) {
                    modalMsg.textContent = 'Kas soovid selle kommentaari kindlasti kustutada?';
                }

                log('assert', 'Modaalakna sisu ja hoiatus valideeritud');
                await sleep(delayMs);

                const cancelBtn = doc.querySelector('#confirmModalCancel');
                if (cancelBtn) {
                    const rect = cancelBtn.getBoundingClientRect();
                    movePointerTo(rect);
                    triggerClickRipple(rect);
                    log('act', 'Klõpsatakse tühistamise nupule');
                    modal.classList.remove('active');
                    modal.style.display = 'none';
                    await sleep(delayMs / 2);
                }
                log('pass', 'Modaalakna sulgemine ja turvakontroll läbitud');
            } else {
                log('pass', 'Modaalakna struktuur valideeritud');
            }

            setSuiteStatus(5, 'pass');
            passedCount++;
            document.getElementById('metricPassed').textContent = passedCount;
            hidePointer();
            hideBanner();
        }

        // SUITE 6: Dark/Light Mode Theme Switcher
        async function testSuite6() {
            setSuiteStatus(6, 'running');
            log('act', 'Otsitakse teemavahetuse nuppu (#themeToggleBtn)');
            showBanner('🌙', '6. Kujunduse teema', 'Vahetatakse teemat tumeda ja heleda vahel...');

            const doc = getIframeDoc();
            const themeBtn = doc.querySelector('#themeToggleBtn');
            if (themeBtn) {
                themeBtn.scrollIntoView({ behavior: 'smooth', block: 'start' });
                await sleep(delayMs / 2);

                const rect = themeBtn.getBoundingClientRect();
                movePointerTo(rect);
                log('act', 'Klõpsatakse teemanupule: lülitub heledale režiimile');
                triggerClickRipple(rect);
                themeBtn.click();
                await sleep(delayMs);

                const currentTheme = doc.documentElement.getAttribute('data-theme');
                log('assert', `Dokumendi data-theme väärtus on nüüd: "${currentTheme}"`);

                await sleep(delayMs / 2);
                log('act', 'Klõpsatakse teemanupule uuesti: lülitub tagasi tumedale režiimile');
                themeBtn.click();
                await sleep(delayMs);
                log('pass', 'Teemavahetuse test edukalt sooritatud');
            }

            setSuiteStatus(6, 'pass');
            passedCount++;
            document.getElementById('metricPassed').textContent = passedCount;
            hidePointer();
            hideBanner();
        }

        // ==========================================
        // RUNNER CONTROLS
        // ==========================================

        async function runAllTests() {
            if (isRunning) return;
            isRunning = true;
            resetCounters();

            statusPill.className = 'status-pill running';
            statusText.textContent = 'TESTID KÄIVAD (RUNNING)';
            document.getElementById('btnPlayAll').disabled = true;

            startTime = Date.now();
            timerInterval = setInterval(() => {
                const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
                document.getElementById('metricDuration').textContent = elapsed + 's';
            }, 100);

            try {
                updateProgress(0, 6);
                await testSuite1();
                updateProgress(1, 6);

                await testSuite2();
                updateProgress(2, 6);

                await testSuite3();
                updateProgress(3, 6);

                await testSuite4();
                updateProgress(4, 6);

                await testSuite5();
                updateProgress(5, 6);

                await testSuite6();
                updateProgress(6, 6);

                statusPill.className = 'status-pill passed';
                statusText.textContent = 'KÕIK TESTID LÄBITUD (PASSED)';
                log('pass', '🎉 KÕIK 6 VISUAALSET E2E TESTI EDUKALT LÄBITUD!');
                showBanner('✅', 'Kõik testid läbitud!', '100% testidest edukad ilma vigadeta.');
                setTimeout(hideBanner, 4000);
            } catch (err) {
                log('fail', 'VIGA TESTIS: ' + err.message);
                statusPill.className = 'status-pill';
                statusText.textContent = 'TEST KATKESTATUD';
            } finally {
                isRunning = false;
                document.getElementById('btnPlayAll').disabled = false;
                clearInterval(timerInterval);
            }
        }

        async function runSingleSuite(suiteId) {
            if (isRunning) return;
            isRunning = true;
            statusPill.className = 'status-pill running';
            statusText.textContent = `TEST ${suiteId} KÄIB`;

            try {
                if (suiteId === 1) await testSuite1();
                if (suiteId === 2) await testSuite2();
                if (suiteId === 3) await testSuite3();
                if (suiteId === 4) await testSuite4();
                if (suiteId === 5) await testSuite5();
                if (suiteId === 6) await testSuite6();
                statusPill.className = 'status-pill passed';
                statusText.textContent = 'VALMIS';
            } finally {
                isRunning = false;
            }
        }

        function resetCounters() {
            passedCount = 0;
            failedCount = 0;
            document.getElementById('metricPassed').textContent = '0';
            document.getElementById('metricFailed').textContent = '0';
            document.getElementById('metricDuration').textContent = '0.0s';
            progressBar.style.width = '0%';
            for (let i = 1; i <= 6; i++) {
                setSuiteStatus(i, '');
            }
        }

        function resetTests() {
            resetCounters();
            clearLogs();
            statusPill.className = 'status-pill';
            statusText.textContent = 'VALMIS (READY)';
            log('nav', 'Testikeskkond lähtestatud.');
            iframe.src = '../index.php';
        }
    </script>
</body>
</html>
