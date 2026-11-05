function createOrbTexture(THREE) {
    const textureCanvas = document.createElement('canvas');
    textureCanvas.width = 1024;
    textureCanvas.height = 512;

    const context = textureCanvas.getContext('2d');

    if (!context) {
        throw new Error('No se pudo crear el contexto de textura para las esferas.');
    }

    // Fondo de la textura de la esfera
    context.fillStyle = '#2b20f8';
    context.fillRect(0, 0, textureCanvas.width, textureCanvas.height);

    const textureWidth = textureCanvas.width;
    const textureHeight = textureCanvas.height;

    // Paleta solo en tonos azules: [r, g, b, opacidad]
    const blues = [
        [10, 0, 150, 0.55],    // azul profundo
        [5, 0, 90, 0.5],       // azul casi negro
        [20, 30, 200, 0.45],   // azul eléctrico
        [60, 70, 230, 0.4],    // azul medio
        [110, 125, 255, 0.3],  // azul claro
    ];
    const pickBlue = () => blues[Math.floor(Math.random() * blues.length)];

    // Borde ondulado e irregular. Usa frecuencias enteras para que la forma
    // se repita sin costura al dar la vuelta completa a la esfera.
    const makeWave = () => {
        const waves = Array.from({ length: 3 }, () => {
            const frequency = 1 + Math.floor(Math.random() * 6);

            return {
                frequency,
                amplitude: (6 + Math.random() * 18) / Math.sqrt(frequency),
                phase: Math.random() * Math.PI * 2,
            };
        });

        return (x) => waves.reduce(
            (sum, wave) => sum + Math.sin((x / textureWidth) * Math.PI * 2 * wave.frequency + wave.phase) * wave.amplitude,
            0,
        );
    };

    // Bandas irregulares: pocas, de grosor variable y con bordes ondulados
    const minBand = 24;  // grosor mínimo (px de textura)
    const maxBand = 90;  // grosor máximo (px de textura)
    const step = 8;      // resolución del contorno

    let bandY = -20;
    while (bandY < textureHeight) {
        const bandHeight = minBand + Math.random() * (maxBand - minBand);
        const [r, g, b, a] = pickBlue();
        const topWave = makeWave();
        const bottomWave = makeWave();

        context.beginPath();
        context.moveTo(0, bandY + topWave(0));
        for (let x = step; x <= textureWidth; x += step) {
            context.lineTo(x, bandY + topWave(x));
        }
        for (let x = textureWidth; x >= 0; x -= step) {
            context.lineTo(x, bandY + bandHeight + bottomWave(x));
        }
        context.closePath();

        // Degradado más amplio: la banda es opaca solo en el centro y se funde hacia los bordes
        const gradient = context.createLinearGradient(0, bandY - 40, 0, bandY + bandHeight + 40);
        gradient.addColorStop(0, `rgba(${r}, ${g}, ${b}, 0)`);
        gradient.addColorStop(0.5, `rgba(${r}, ${g}, ${b}, ${a})`);
        gradient.addColorStop(1, `rgba(${r}, ${g}, ${b}, 0)`);
        context.fillStyle = gradient;
        context.fill();

        bandY += bandHeight * (0.6 + Math.random() * 0.5);
    }

    // Manchas alargadas y difusas para romper la regularidad (como nubes de gas)
    const blotchCount = 45;
    for (let index = 0; index < blotchCount; index += 1) {
        const x = Math.random() * textureWidth;
        const y = Math.random() * textureHeight;
        const radius = 24 + Math.random() * 50;
        const stretch = 2.5 + Math.random() * 5; // cuanto más alto, más alargada en horizontal
        const [r, g, b] = pickBlue();
        const alpha = 0.15 + Math.random() * 0.2;

        // Se dibuja también desplazada una vuelta a cada lado para que no haya corte en el borde
        for (const offset of [-textureWidth, 0, textureWidth]) {
            context.save();
            context.translate(x + offset, y);
            context.scale(stretch, 1);

            const blotch = context.createRadialGradient(0, 0, 0, 0, 0, radius);
            blotch.addColorStop(0, `rgba(${r}, ${g}, ${b}, ${alpha})`);
            blotch.addColorStop(1, `rgba(${r}, ${g}, ${b}, 0)`);
            context.fillStyle = blotch;
            context.beginPath();
            context.arc(0, 0, radius, 0, Math.PI * 2);
            context.fill();
            context.restore();
        }
    }

    // Desenfoque final: se reduce la textura y se vuelve a ampliar suavizada.
    // Funciona en todos los navegadores (context.filter no va en Safari).
    // 1 = sin desenfoque, 4 = suave, 8 = muy difuminado.
    const blurFactor = 2.3;

    if (blurFactor > 1) {
        const smallCanvas = document.createElement('canvas');
        smallCanvas.width = Math.max(1, Math.round(textureWidth / blurFactor));
        smallCanvas.height = Math.max(1, Math.round(textureHeight / blurFactor));

        const smallContext = smallCanvas.getContext('2d');

        if (smallContext) {
            smallContext.imageSmoothingEnabled = true;
            smallContext.imageSmoothingQuality = 'high';
            smallContext.drawImage(textureCanvas, 0, 0, smallCanvas.width, smallCanvas.height);

            context.imageSmoothingEnabled = true;
            context.imageSmoothingQuality = 'high';
            context.drawImage(smallCanvas, 0, 0, textureWidth, textureHeight);
        }
    }

    const texture = new THREE.CanvasTexture(textureCanvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.anisotropy = 4;

    return texture;
}

function createShadowTexture(THREE) {
    const size = 256;
    const shadowCanvas = document.createElement('canvas');
    shadowCanvas.width = size;
    shadowCanvas.height = size;

    const ctx = shadowCanvas.getContext('2d');

    if (!ctx) {
        throw new Error('No se pudo crear el contexto de textura para las sombras.');
    }

    const gradient = ctx.createRadialGradient(size / 2, size / 2, 0, size / 2, size / 2, size / 2);
    gradient.addColorStop(0, 'rgba(10, 0, 90, 0.55)');
    gradient.addColorStop(0.5, 'rgba(10, 0, 90, 0.22)');
    gradient.addColorStop(1, 'rgba(10, 0, 90, 0)');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, size, size);

    return new THREE.CanvasTexture(shadowCanvas);
}

function createShadow(THREE, texture, width, height) {
    const mesh = new THREE.Mesh(
        new THREE.PlaneGeometry(1, 1),
        new THREE.MeshBasicMaterial({
            map: texture,
            transparent: true,
            depthWrite: false,
            toneMapped: false,
        }),
    );

    mesh.scale.set(width, height, 1);
    mesh.userData.baseScale = { x: width, y: height };
    mesh.position.z = -1;

    return mesh;
}

function createSphere(THREE, texture, radius, segments) {
    const geometry = new THREE.SphereGeometry(radius, segments, segments);
    const material = new THREE.MeshStandardMaterial({
        color: '#ffffff',
        map: texture,
        roughness: 0.9,
        metalness: 0,
        bumpMap: texture,
        bumpScale: 0.06,
    });

    return new THREE.Mesh(geometry, material);
}

export async function initOrbScene() {
    const stage = document.querySelector('[data-orb-scene]');
    const canvas = stage?.querySelector('[data-orb-canvas]');

    if (!stage || !canvas) {
        return;
    }

    let THREE;
    let renderer;

    try {
        THREE = await import('three');
        renderer = new THREE.WebGLRenderer({
            canvas,
            alpha: true,
            antialias: window.devicePixelRatio <= 1.5,
            powerPreference: 'low-power',
        });
    } catch (error) {
        console.warn('La escena 3D no está disponible; se muestran las esferas de respaldo.', error);
        stage.querySelectorAll('[data-orb-fallback]').forEach((sphere) => {
            sphere.hidden = false;
        });
        return;
    }

    const scene = new THREE.Scene();
    const camera = new THREE.OrthographicCamera(-2.8, 2.8, 2.8, -2.8, 0.1, 100);
    // Inclinación de la cámara: mira desde un poco más arriba (en radianes).
    // 0 = de frente, 0.3 ≈ 17°, 0.5 ≈ 29°
    const cameraTilt = 0.3;
    const cameraDistance = 10.5;
    camera.position.set(0, Math.sin(cameraTilt) * cameraDistance, Math.cos(cameraTilt) * cameraDistance);
    camera.lookAt(0, 0, 0);

    // Esferas
    const texture = createOrbTexture(THREE);
    const largeSphere = createSphere(THREE, texture, 1.65, 80);
    const smallSphere = createSphere(THREE, texture, 0.6, 56);
    largeSphere.position.set(-0.75, -0.4, 0);
    smallSphere.position.set(1.65, 1.35, 0);
    scene.add(largeSphere, smallSphere);

    // Sombras
    const shadowTexture = createShadowTexture(THREE);
    // Las sombras ahora están tumbadas sobre un "suelo" (plano horizontal),
    // así la inclinación de la cámara les da perspectiva de forma natural.
    // El segundo valor es la profundidad (eje Z) de la sombra sobre el suelo.
    const floorY = -2.15;
    const largeShadow = createShadow(THREE, shadowTexture, 3.6, 2.4);
    const smallShadow = createShadow(THREE, shadowTexture, 1.4, 1.1);
    largeShadow.rotation.x = -Math.PI / 2;
    smallShadow.rotation.x = -Math.PI / 2;
    largeShadow.position.set(-0.75, floorY, 0);
    smallShadow.position.set(1.65, floorY, 0);
    largeShadow.material.opacity = 1;
    smallShadow.material.opacity = 0.6; // más alta, sombra más tenue
    scene.add(largeShadow, smallShadow);

    // Luces
    scene.add(new THREE.HemisphereLight('#fffaf0', '#26327f', 2.15));

    const keyLight = new THREE.DirectionalLight('#fff4df', 3.2);
    keyLight.position.set(-4, 6, 7);
    scene.add(keyLight);

    const cobaltFill = new THREE.PointLight('#3a4ed1', 18, 18);
    cobaltFill.position.set(4, -1, 3);
    scene.add(cobaltFill);

    const rimLight = new THREE.PointLight('#2838b3', 12, 16);
    rimLight.position.set(-5, 1, -3);
    scene.add(rimLight);

    renderer.setClearColor('#f4efe6', 0);
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const mobileViewport = window.matchMedia('(max-width: 48rem)');
    const pointer = { x: 0, y: 0 };
    const pointerTarget = { x: 0, y: 0 };
    let visible = false;
    let disposed = false;
    let frameId = 0;
    let elapsed = 0;
    let previousTime = 0;
    let resizeObserver;
    let intersectionObserver;

    const resize = () => {
        const width = stage.clientWidth;
        const height = stage.clientHeight;

        if (!width || !height || disposed) {
            return;
        }

        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, mobileViewport.matches ? 1 : 1.5));
        renderer.setSize(width, height, false);
        camera.left = -2.8 * width / height;
        camera.right = 2.8 * width / height;
        camera.updateProjectionMatrix();
        renderer.render(scene, camera);
    };

    const animate = (time) => {
        frameId = 0;

        if (disposed || !visible || document.hidden) {
            return;
        }

        const delta = previousTime ? Math.min((time - previousTime) / 1000, 0.05) : 0;
        previousTime = time;
        elapsed += delta;
        pointer.x += (pointerTarget.x - pointer.x) * Math.min(delta * 2, 1);
        pointer.y += (pointerTarget.y - pointer.y) * Math.min(delta * 2, 1);

        largeSphere.rotation.y = elapsed * 0.11 + pointer.x * 0.035;
        largeSphere.rotation.x = Math.sin(elapsed * 0.19) * 0.025 + pointer.y * 0.025;
        largeSphere.position.y = -0.4 + Math.sin(elapsed * 0.55) * 0.06;
        smallSphere.rotation.y = elapsed * -0.2;
        smallSphere.position.x = 1.65 + Math.sin(elapsed * 0.3) * 0.06;
        smallSphere.position.y = 1.35 + Math.sin(elapsed * 0.7) * 0.06;

        // La sombra se encoge cuando la esfera sube
        const largeFactor = 1 - Math.sin(elapsed * 0.55) * 0.08;
        largeShadow.scale.set(
            largeShadow.userData.baseScale.x * largeFactor,
            largeShadow.userData.baseScale.y * largeFactor,
            1,
        );

        const smallFactor = 1 - Math.sin(elapsed * 0.7) * 0.1;
        smallShadow.scale.set(
            smallShadow.userData.baseScale.x * smallFactor,
            smallShadow.userData.baseScale.y * smallFactor,
            1,
        );

        renderer.render(scene, camera);
        frameId = window.requestAnimationFrame(animate);
    };

    const resume = () => {
        if (disposed || !visible || document.hidden) {
            return;
        }

        if (reducedMotion) {
            renderer.render(scene, camera);
            return;
        }

        if (!frameId) {
            previousTime = 0;
            frameId = window.requestAnimationFrame(animate);
        }
    };

    const pause = () => {
        if (frameId) {
            window.cancelAnimationFrame(frameId);
            frameId = 0;
        }

        previousTime = 0;
    };

    const onPointerMove = (event) => {
        const bounds = stage.getBoundingClientRect();
        pointerTarget.x = ((event.clientX - bounds.left) / bounds.width - 0.5) * 2;
        pointerTarget.y = ((event.clientY - bounds.top) / bounds.height - 0.5) * 2;
    };

    const onVisibilityChange = () => (document.hidden ? pause() : resume());

    const dispose = () => {
        disposed = true;
        pause();
        resizeObserver?.disconnect();
        intersectionObserver?.disconnect();
        mobileViewport.removeEventListener('change', resize);
        document.removeEventListener('visibilitychange', onVisibilityChange);
        stage.removeEventListener('pointermove', onPointerMove);
        window.removeEventListener('pagehide', dispose);

        scene.traverse((object) => {
            if (object.geometry) {
                object.geometry.dispose();
            }

            if (object.material) {
                const materials = Array.isArray(object.material) ? object.material : [object.material];
                materials.forEach((material) => material.dispose());
            }
        });

        texture.dispose();
        shadowTexture.dispose();
        renderer.renderLists.dispose();
        renderer.dispose();
        renderer.forceContextLoss();
    };

    resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(stage);
    intersectionObserver = new IntersectionObserver(([entry]) => {
        visible = entry.isIntersecting;
        visible ? resume() : pause();
    }, { threshold: 0.05 });
    intersectionObserver.observe(stage);
    mobileViewport.addEventListener('change', resize);
    document.addEventListener('visibilitychange', onVisibilityChange);
    stage.addEventListener('pointermove', onPointerMove, { passive: true });
    window.addEventListener('pagehide', dispose, { once: true });

    resize();
    stage.querySelectorAll('[data-orb-fallback]').forEach((sphere) => {
        sphere.hidden = true;
    });
}