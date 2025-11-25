<?php
// ruleta_svg_realista.php
// Versión realista (SVG) — guarda y abre en servidorlocal (XAMPP/Apache)

$numeroGanador = null;
$resultado = null;

$numbers = [
    0,32,15,19,4,21,2,25,17,34,6,27,13,36,11,30,8,23,10,5,24,16,33,
    1,20,14,31,9,22,18,29,7,28,12,35,3,26
]; // orden europeo (single zero)

if (isset($_POST['apostar'])) {
    $numeroJugador = (int)$_POST['numero'];
    $numeroGanador = $numbers[array_rand($numbers)]; // elegir aleatorio en la ruleta real
    // También se puede usar rand(0,36) pero así respetamos la disposición
    if ($numeroJugador === $numeroGanador) {
        $resultado = "🎉 ¡Ganaste! El número fue $numeroGanador.";
    } else {
        $resultado = "😢 Perdiste. El número ganador fue $numeroGanador.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Ruleta Realista (SVG)</title>
    <style>
        :root {
            --size: 520px;
        }
        body {
            background: radial-gradient(circle at 30% 30%, #0b4d2e 0%, #032414 70%);
            color: #fff;
            font-family: Inter, system-ui, Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
            padding: 20px;
        }
        h1 { margin: 0; text-shadow: 0 2px 6px rgba(0,0,0,.8); }
        .wrap { display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap; justify-content:center; }
        .canvas {
            width: var(--size);
            height: var(--size);
            position: relative;
            user-select: none;
        }
        svg { width:100%; height:100%; display:block; }
        .controls {
            width: 320px;
            background: rgba(255,255,255,0.04);
            padding: 12px;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.6);
        }
        input[type=number] {
            width:100%;
            padding:8px 10px;
            border-radius:6px;
            border: none;
            font-size:1rem;
        }
        button {
            margin-top:10px;
            width:100%;
            padding:10px;
            border-radius:8px;
            background: linear-gradient(180deg,#c81919,#8b0f0f);
            border: none;
            color: white;
            font-weight:600;
            cursor:pointer;
        }
        .message {
            min-height:24px;
            margin-top:8px;
            text-align:center;
            font-weight:600;
        }

        /* puntero (indicator) */
        .indicator {
            position: absolute;
            left:50%;
            top:6px;
            transform:translateX(-50%);
            width: 0;
            height: 0;
            border-left: 18px solid transparent;
            border-right: 18px solid transparent;
            border-bottom: 28px solid #f7d84b;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,.6));
            z-index: 10;
        }

        /* animación por JS (se aplicará a los grupos SVG) */
        .wheel-rot { transition: transform 4.5s cubic-bezier(.12,.68,.24,1); transform-origin: 50% 50%; }
        .ball-rot { transition: transform 4.5s cubic-bezier(.12,.68,.24,1); transform-origin: 50% 50%; }
        .ball-drop { transition: transform 0.9s cubic-bezier(.22,.86,.32,1); } /* caída radial */
        .glow { filter: drop-shadow(0 0 10px rgba(255,255,255,0.8)); }
    </style>
</head>
<body>
<h1>🎯 Ruleta Realista (SVG)</h1>

<div class="wrap">
    <div class="canvas">
        <div class="indicator"></div>
        <!-- SVG RUEDA -->
        <svg id="svgWheel" viewBox="0 0 520 520" xmlns="http://www.w3.org/2000/svg" aria-label="Ruleta">
            <!-- defs para gradientes/sombras -->
            <defs>
                <radialGradient id="rimGrad" cx="50%" cy="30%">
                    <stop offset="0%" stop-color="#f6f3ea"/>
                    <stop offset="100%" stop-color="#c4b99f"/>
                </radialGradient>
                <radialGradient id="centerGrad">
                    <stop offset="0%" stop-color="#fff"/>
                    <stop offset="100%" stop-color="#b8b2a0"/>
                </radialGradient>
                <filter id="softShadow" x="-50%" y="-50%" width="200%" height="200%">
                    <feDropShadow dx="0" dy="8" stdDeviation="14" flood-color="#000" flood-opacity="0.6"/>
                </filter>
            </defs>

            <!-- fondo circular -->
            <g id="wheelGroup" class="wheel-rot" transform="translate(260,260)">
                <circle r="240" fill="#111" stroke="#222" stroke-width="8" />

                <!-- pockets -->
                <g id="pockets">
                    <!-- Generated pockets via JS/CSS transform will be replaced by elements created by JS for clarity -->
                </g>

                <!-- rim (anillo) -->
                <circle r="200" fill="url(#rimGrad)" stroke="#2b2b2b" stroke-width="6" />

                <!-- numbers ring (texts) -->
                <g id="numbers" />

                <!-- center cap -->
                <circle r="60" fill="url(#centerGrad)" stroke="#5a5144" stroke-width="4" />
                <circle r="40" fill="#0b3e2a" stroke="#142b20" stroke-width="3" />

            </g>

            <!-- BOLA: colocada por transform (rotate(angle) translate(radius) rotate(-angle)) -->
            <g id="ballGroup" class="ball-rot" transform="translate(260,260)">
                <g id="ballWrapper" transform="rotate(0)">
                    <g id="ballPos" transform="translate(0,-210)">
                        <circle id="ball" r="10" fill="#fff" stroke="#ddd" stroke-width="1.6" />
                        <circle r="4" fill="#f2f2f2" transform="translate(-3,-3)" opacity="0.9"/>
                    </g>
                </g>
            </g>
        </svg>
    </div>

    <div class="controls">
        <form method="POST" id="betForm">
            <label for="numero">Elige un número (0–36):</label>
            <input id="numero" name="numero" type="number" min="0" max="36" required />
            <button type="submit" name="apostar">Girar Ruleta</button>
        </form>
        <div class="message" id="message"><?= $resultado ? htmlspecialchars($resultado) : '&nbsp;' ?></div>
        <div style="font-size:12px; margin-top:8px; color: #ddd;">
            <strong>Número ganador (servidor):</strong>
            <span id="serverNumber"><?= $numeroGanador !== null ? $numeroGanador : '-' ?></span>
        </div>
    </div>
</div>

<script>
    /*
     JS controlador de la animación SVG (versión 'realista' visual)
     - La disposición de los números es la misma que en PHP (array 'numbers').
     - Cuando PHP devuelve $numeroGanador, la animación se dispara y la rueda/bola
       realizan un gran giro y la bola "cae" en la casilla correcta.
     - Todo visual; la física está simulada (curvas de easing, desaceleración, caída radial).
    */

    const numbers = <?= json_encode($numbers, JSON_NUMERIC_CHECK) ?>;
    const pocketsCount = numbers.length; // 37
    const svgNS = "http://www.w3.org/2000/svg";

    const wheelGroup = document.getElementById('wheelGroup');
    const numbersGroup = document.getElementById('numbers');
    const pocketsGroup = document.getElementById('pockets');
    const ballGroup = document.getElementById('ballGroup');
    const ballWrapper = document.getElementById('ballWrapper');
    const ballPos = document.getElementById('ballPos');
    const serverNumberEl = document.getElementById('serverNumber');
    const messageEl = document.getElementById('message');

    const size = 520;
    const cx = 0, cy = 0;
    const outerRadius = 200;
    const textRadius = 168;
    const pocketAngle = 360 / pocketsCount;

    // crear pockets (colores) y números alrededor
    function createWheelGraphics() {
        pocketsGroup.innerHTML = '';
        numbersGroup.innerHTML = '';
        for (let i = 0; i < pocketsCount; i++) {
            const angle = i * pocketAngle;
            // color: green for 0, then alternated red/black following european pattern
            const num = numbers[i];
            let color = '#000';
            if (num === 0) color = '#0a7a3a'; // green
            else {
                // Determine red/black using a precomputed map by number value parity isn't correct;
                // But simpler: use the european alternating pattern by index (common visual).
                // We'll use sequence: starting after 0 is red, then black, alternate.
                color = (i % 2 === 1) ? '#b32424' : '#000';
            }

            // pocket wedge (thin slice): draw as path using arc - simpler: use rotated rect
            const wedge = document.createElementNS(svgNS, 'path');
            // compute wedge by polar coordinates
            const a1 = (angle - pocketAngle/2) * Math.PI/180;
            const a2 = (angle + pocketAngle/2) * Math.PI/180;
            const r1 = 200, r2 = 120;
            const x1 = Math.cos(a1)*r1, y1 = Math.sin(a1)*r1;
            const x2 = Math.cos(a2)*r1, y2 = Math.sin(a2)*r1;
            const x3 = Math.cos(a2)*r2, y3 = Math.sin(a2)*r2;
            const x4 = Math.cos(a1)*r2, y4 = Math.sin(a1)*r2;
            const d = `M ${x1} ${y1} A ${r1} ${r1} 0 0 1 ${x2} ${y2} L ${x3} ${y3} A ${r2} ${r2} 0 0 0 ${x4} ${y4} Z`;
            wedge.setAttribute('d', d);
            wedge.setAttribute('fill', color);
            wedge.setAttribute('stroke', '#222');
            wedge.setAttribute('stroke-width', '1');
            pocketsGroup.appendChild(wedge);

            // number text
            const text = document.createElementNS(svgNS, 'text');
            const theta = angle * Math.PI/180;
            const tx = Math.cos(theta) * textRadius;
            const ty = Math.sin(theta) * textRadius;
            text.setAttribute('x', tx);
            text.setAttribute('y', ty + 6); // ajuste vertical
            text.setAttribute('text-anchor', 'middle');
            text.setAttribute('font-size', '14');
            text.setAttribute('font-weight', '700');
            text.setAttribute('fill', (num === 0 ? '#fff' : '#fff'));
            text.setAttribute('transform', `rotate(${angle}, ${tx}, ${ty})`);
            text.textContent = num;
            numbersGroup.appendChild(text);
        }
    }

    // animar la rueda y la bola para aterrizar en 'targetNumber'
    function spinToNumber(targetNumber) {
        if (targetNumber === null || typeof targetNumber === 'undefined') return;

        // localizar índice del número en la disposición
        const targetIndex = numbers.indexOf(Number(targetNumber));
        if (targetIndex === -1) return; // seguridad

        // Cálculos de rotación:
        // Queremos que tras la animación la ranura targetIndex esté exactamente en la posición del indicador (arriba)
        // Si el wheelGroup rota X grados, la ranura en index i alcanzará ángulo = (i * pocketAngle - X) mod 360 relative indicator
        // Así, para que targetIndex esté en 0º, X debe ser i * pocketAngle modulo 360 (más vueltas completas).
        const targetAngle = targetIndex * pocketAngle; // ángulo objetivo en grados

        // Elegimos vueltas grandes para realismo
        const wheelSpins = 8; // vueltas completas
        const ballSpins = 12; // la bola da más vueltas inicialmente

        // Añadir pequeñas aleatoriedades visuales para que no parezca demasiado mecánico
        const rndOffset = (Math.random() * (pocketAngle*0.6)) - (pocketAngle*0.3);

        // Wheel final rotation (en grados). Usamos negativo para rotar en sentido horario visual.
        const wheelFinalDeg = -(wheelSpins * 360 + targetAngle + rndOffset);
        const ballFinalDeg  = (ballSpins * 360 + targetAngle + rndOffset*0.6); // bola gira en sentido contrario

        // Duraciones ligeramente diferentes para sensación realista
        const wheelDuration = 4800 + Math.floor(Math.random()*600); // ms
        const ballDuration  = 4200 + Math.floor(Math.random()*700);

        // Aplicar transform con transición (clase .wheel-rot y .ball-rot ya tienen transición)
        wheelGroup.style.transitionDuration = wheelDuration + 'ms';
        ballGroup.style.transitionDuration  = ballDuration  + 'ms';

        // Rotar wheel & ball wrapper
        wheelGroup.style.transform = `translate(260px,260px) rotate(${wheelFinalDeg}deg)`;
        // For ball, we rotate the wrapper opposite so it appears to move relative to wheel
        ballWrapper.style.transform = `rotate(${ballFinalDeg}deg)`;

        // Animar la "caída" de la bola: primero la bola gira en el borde; cuando termine, hacemos la caída radial (inward)
        // Programamos eventos para cuando terminen las transiciones
        let wheelDone = false, ballDone = false;
        const onEnd = () => {
            if (!wheelDone || !ballDone) return;
            // Hacer caída radial — mover ballPos desde radius ~210 a radius ~90 (center area)
            // Aplicamos clase con transición en transform para producir efecto de caída
            ballPos.parentElement.classList.add('ball-drop'); // no hace nada por sí sola, pero usamos inline transform
            // animate via transform on ballPos (translate(0,-r))
            // CSS transition .ball-drop defined; here we directly set transform style
            // Final radius (where ball 'encaja') cerca del borde interior of pockets (r ~ 138)
            const finalRadius = 120 + Math.random()*10; // pequeños variaciones
            // set transform to translate(0, -finalRadius)
            ballPos.style.transition = 'transform 900ms cubic-bezier(.22,.86,.32,1)';
            ballPos.style.transform = `translate(0, -${finalRadius})`;

            // Después de la caída, destellar y fijar la bolita sobre el número
            setTimeout(() => {
                // aplicar pequeño rebote y glow
                const ball = document.getElementById('ball');
                ball.style.transition = 'transform 250ms ease';
                ball.style.transform = 'translateY(-6px)'; // pequeño rebote visual
                ball.classList.add('glow');
                setTimeout(() => {
                    ball.style.transform = '';
                }, 250);
            }, 920);
        };

        // Listeners para transiciones
        const wheelTransitionEnd = () => { wheelDone = true; wheelGroup.removeEventListener('transitionend', wheelTransitionEnd); onEnd(); };
        const ballTransitionEnd  = () => { ballDone = true; ballGroup.removeEventListener('transitionend', ballTransitionEnd); onEnd(); };
        wheelGroup.addEventListener('transitionend', wheelTransitionEnd);
        ballGroup.addEventListener('transitionend', ballTransitionEnd);
    }

    // inicialización
    createWheelGraphics();

    // Si el servidor ya determinó un ganador, lanzamos la animación.
    // PHP inyecta el número ganador en el DOM; lo leemos:
    const serverNumber = (serverNumberEl && serverNumberEl.textContent.trim() !== '-') ? Number(serverNumberEl.textContent) : null;

    if (serverNumber !== null && !isNaN(serverNumber)) {
        // Pequeño delay para que el usuario vea la rueda parada antes de girar
        setTimeout(() => spinToNumber(serverNumber), 300);
    }

    // Evitar recarga si el usuario no configura un número correcto (cliente)
    document.getElementById('betForm').addEventListener('submit', function(e) {
        const inNum = Number(document.getElementById('numero').value);
        if (isNaN(inNum) || inNum < 0 || inNum > 36) {
            e.preventDefault();
            messageEl.textContent = 'Introduce un número válido entre 0 y 36.';
            setTimeout(()=> messageEl.textContent = '', 2000);
            return false;
        }
        // permite enviar — PHP recargará la página y la animación se reproducirá con el número servidor
    });
</script>
</body>
</html>
