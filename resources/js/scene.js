function createOrbTexture(THREE) {
    const textureCanvas = document.createElement('canvas');
    textureCanvas.width = 1024;
    textureCanvas.height = 512;

    const context = textureCanvas.getContext('2d');

    if (!context) {
        throw new Error('No se pudo crear el contexto de textura para las esferas.');
    }

    // Fondo de la textura de la esfera
    context.fillStyle = '#0a00cb';
    context.fillRect(0, 0, textureCanvas.width, textureCanvas.height);

    for (let index = 0; index < 1400; index += 1) {
        const x = Math.random() * textureCanvas.width;
        const y = Math.random() * textureCanvas.height;
        const width = 8 + Math.random() * 36;

        context.beginPath();
        context.strokeStyle = index % 4 === 0 ? 'rgba(158, 0, 0, 0.2)' : 'rgba(139, 76, 11, 0.12)';
        context.lineWidth = 0.5 + Math.random() * 1.4;
        context.moveTo(x, y);
        context.bezierCurveTo(x + width * 0.25, y - 2, x + width * 0.7, y + 3, x + width, y);
        context.stroke();
    }

    const texture = new THREE.CanvasTexture(textureCanvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.anisotropy = 4;

    return texture;
}

function createSphere(THREE, texture, radius, segments) {
    const geometry = new THREE.SphereGeometry(radius, segments, segments);
    const material = new THREE.MeshStandardMaterial({
        color: '#ffffff',
        map: texture,
        roughness: 0.82,
        metalness: 0,
        bumpMap: texture,
        bumpScale: 0.022,
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
        return;
    }

    const scene = new THREE.Scene();
    const camera = new THREE.OrthographicCamera(-2.8, 2.8, 2.8, -2.8, 0.1, 100);
    camera.position.set(0, 0, 10.5);

    const texture = createOrbTexture(THREE);
    const largeSphere = createSphere(THREE, texture, 1.65, 80);
    const smallSphere = createSphere(THREE, texture, 0.6, 56);
    largeSphere.position.set(-0.75, -0.4, 0);
    smallSphere.position.set(1.65, 1.35, 0);
    scene.add(largeSphere, smallSphere);

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

