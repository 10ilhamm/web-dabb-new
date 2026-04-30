let currentRoom = null;
let currentView = 'front';
let currentRotationX = 0;
let currentRotationY = 0;
let isDragging = false;
let previousMousePosition = { x: 0, y: 0 };
let dragStartPos = { x: 0, y: 0 };

let currentZoom = 600;

document.addEventListener('DOMContentLoaded', () => {
    // Setup view switch buttons
    document.querySelectorAll('.vt3d-view-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const v = e.target.dataset.view;
            setView(v);
        });
    });

    // Removed redundant door event listener.

    // --- Drag to rotate logic ---
    const wrapper = document.getElementById('vt3d-scene-wrapper');
    const scene = document.getElementById('vt3d-scene');

    if (wrapper && scene) {
        wrapper.style.cursor = 'grab';

        // Mouse Events
        wrapper.addEventListener('mousedown', (e) => {
            isDragging = true;
            dragStartPos = { x: e.clientX, y: e.clientY };
            previousMousePosition = { x: e.clientX, y: e.clientY };
            wrapper.style.cursor = 'grabbing';
            scene.style.transition = 'none'; // remove transition for smooth drag
        });

        window.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            const deltaMove = {
                x: e.clientX - previousMousePosition.x,
                y: e.clientY - previousMousePosition.y
            };

            currentRotationY += deltaMove.x * 0.25;
            currentRotationX -= deltaMove.y * 0.25;

            // Limit up/down angle to avoid flipping
            currentRotationX = Math.max(-25, Math.min(25, currentRotationX));

            updateSceneTransform();

            previousMousePosition = { x: e.clientX, y: e.clientY };
        });

        window.addEventListener('mouseup', (e) => {
            if (isDragging) {
                isDragging = false;
                wrapper.style.cursor = 'grab';
                scene.style.transition = 'transform 0.4s ease-out';

                // Robust manual 2D hit-test for the door avoiding CSS 3D shielding bugs
                const dist = Math.hypot(e.clientX - dragStartPos.x, e.clientY - dragStartPos.y);
                if (dist < 5) {
                    // Find all active door slots
                    const activeDoors = document.querySelectorAll('.vt3d-door-slot[style*="display: block"], .vt3d-door-slot[style*="display:block"]');
                    activeDoors.forEach(activeDoor => {
                        const rect = activeDoor.getBoundingClientRect();
                        // Check if click is inside door's 2D screen bounding box
                        if (e.clientX >= rect.left && e.clientX <= rect.right &&
                            e.clientY >= rect.top && e.clientY <= rect.bottom) {
                            
                            // Check if the door's wall is actually facing the camera
                            const doorWall = activeDoor.dataset.wall;
                            let rotY = ((currentRotationY % 360) + 360) % 360;
                            let isFacing = false;
                            if (doorWall === 'back')  isFacing = (rotY > 90 && rotY < 270);
                            if (doorWall === 'front') isFacing = (rotY < 90 || rotY > 270);
                            if (doorWall === 'left')  isFacing = (rotY > 0 && rotY < 180);
                            if (doorWall === 'right') isFacing = (rotY > 180 && rotY < 360);
                            
                            if (isFacing) {
                                window.handleDoorClick(e, activeDoor);
                            }
                        }
                    });
                }
            }
        });

        // Zoom with Mouse Wheel
        wrapper.addEventListener('wheel', (e) => {
            e.preventDefault();
            const zoomSpeed = 0.5;
            currentZoom -= e.deltaY * zoomSpeed; // Scroll up (neg delta) bounds to Zoom In (higher Z)
            // Limit zoom distance (lower = zoom out, higher = zoom in)
            currentZoom = Math.max(200, Math.min(1200, currentZoom));
            
            updateSceneTransform();
        }, { passive: false });

        // Touch Events for mobile
        wrapper.addEventListener('touchstart', (e) => {
            isDragging = true;
            dragStartPos = { x: e.touches[0].clientX, y: e.touches[0].clientY };
            previousMousePosition = { x: e.touches[0].clientX, y: e.touches[0].clientY };
            scene.style.transition = 'none';
        });

        window.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            // Prevent scrolling while panning 3D viewer
            e.preventDefault();

            const deltaMove = {
                x: e.touches[0].clientX - previousMousePosition.x,
                y: e.touches[0].clientY - previousMousePosition.y
            };

            currentRotationY += deltaMove.x * 0.4;
            currentRotationX -= deltaMove.y * 0.4;

            currentRotationX = Math.max(-25, Math.min(25, currentRotationX));

            updateSceneTransform();

            previousMousePosition = { x: e.touches[0].clientX, y: e.touches[0].clientY };
        }, { passive: false });

        window.addEventListener('touchend', (e) => {
            if (isDragging) {
                isDragging = false;
                scene.style.transition = 'transform 0.4s ease-out';

                if (e.changedTouches && e.changedTouches.length > 0) {
                    const touch = e.changedTouches[0];
                    const dist = Math.hypot(touch.clientX - dragStartPos.x, touch.clientY - dragStartPos.y);
                    if (dist < 10) {
                        const activeDoors = document.querySelectorAll('.vt3d-door-slot[style*="display: block"], .vt3d-door-slot[style*="display:block"]');
                        activeDoors.forEach(activeDoor => {
                            const rect = activeDoor.getBoundingClientRect();
                            if (touch.clientX >= rect.left && touch.clientX <= rect.right &&
                                touch.clientY >= rect.top && touch.clientY <= rect.bottom) {
                                
                                const doorWall = activeDoor.dataset.wall;
                                let rotY = ((currentRotationY % 360) + 360) % 360;
                                let isFacing = false;
                                if (doorWall === 'back')  isFacing = (rotY > 90 && rotY < 270);
                                if (doorWall === 'front') isFacing = (rotY < 90 || rotY > 270);
                                if (doorWall === 'left')  isFacing = (rotY > 0 && rotY < 180);
                                if (doorWall === 'right') isFacing = (rotY > 180 && rotY < 360);
                                
                                if (isFacing) {
                                    window.handleDoorClick(e, activeDoor);
                                }
                            }
                        });
                    }
                }
            }
        });
    }
});

function openRoom3D(roomId) {
    currentRoom = window.virtualRooms3D.find(r => r.id === roomId);
    if (!currentRoom) return;

    // Reset view state
    currentZoom = 600;
    currentRotationX = 0;
    currentRotationY = 0;

    // Apply colors
    document.getElementById('wallEditor') && document.getElementById('wallEditor').style.setProperty('background-color', currentRoom.wall_color);
    document.querySelectorAll('.vt3d-wall').forEach(w => w.style.backgroundColor = currentRoom.wall_color);
    const floor = document.getElementById('vt3d-floor');
    if (floor) floor.style.backgroundColor = currentRoom.floor_color || '#8B7355';
    
    const ceiling = document.getElementById('vt3d-ceiling');
    if (ceiling) ceiling.style.backgroundColor = currentRoom.ceiling_color || '#f5f5f5';

    // Render Media
    renderRoomMedia();

    // Render Doors on their respective walls
    document.querySelectorAll('.vt3d-door-slot').forEach(slot => {
        slot.style.display = 'none';
        const wall = slot.dataset.wall;
        const config = (currentRoom.doors && currentRoom.doors[wall]) ? currentRoom.doors[wall] : null;

        if (config && config.link_type && config.link_type !== 'none') {
            slot.style.display = 'block';
            const doorLabel = slot.querySelector('.vt3d-door-label');
            if (doorLabel) {
                doorLabel.innerText = currentRoom.door_labels
                    ? (currentRoom.door_labels[wall] || config.label || '')
                    : (currentRoom.door_label || config.label || '');
            }
            
            // Setup Peek/Portal effect for door
            const doorPortal = slot.querySelector('.vt3d-door-portal');
            if (doorPortal) {
                doorPortal.style.display = 'none';
                slot.style.backgroundColor = '#000'; // default void
                
                if (config.link_type === 'room') {
                    const targetId = parseInt(config.target);
                    const targetRoom = window.virtualRooms3D.find(r => r.id === targetId);
                    if (targetRoom) {
                        if (targetRoom.thumbnail_url) {
                            doorPortal.src = targetRoom.thumbnail_url;
                            doorPortal.style.display = 'block';
                        } else {
                            slot.style.backgroundColor = targetRoom.wall_color || '#1e293b';
                        }
                    }
                } else if (config.link_type === 'url') {
                    slot.style.backgroundColor = '#e0f2fe'; // Bright light for external URL
                }
            }
        }
    });

    // Show viewer and reset view
    document.getElementById('room3d-viewer').style.display = 'block';
    
    document.getElementById('vt3d-scene').style.transition = 'none';
    setView('front');
    setTimeout(() => {
        document.getElementById('vt3d-scene').style.transition = 'transform 0.4s ease-out';
    }, 50);
    
    // Auto-play videos if any
    document.querySelectorAll('#room3d-viewer video').forEach(v => {
        v.play().catch(e => console.log('Auto-play blocked'));
    });
}

function closeRoom3D() {
    document.getElementById('room3d-viewer').style.display = 'none';
    
    // Pause videos
    document.querySelectorAll('#room3d-viewer video').forEach(v => v.pause());
}

function renderRoomMedia() {
    // Clear old media
    document.querySelectorAll('.vt3d-media-layer').forEach(layer => layer.innerHTML = '');

    if (!currentRoom.media) return;

    currentRoom.media.forEach(m => {
        const layer = document.querySelector(`.vt3d-media-layer[data-wall="${m.wall}"]`);
        if (!layer) return;

        const wrapper = document.createElement('div');
        wrapper.style.position = 'absolute';
        wrapper.style.left = m.position_x + '%';
        wrapper.style.top = m.position_y + '%';
        wrapper.style.width = m.width + '%';
        wrapper.style.height = m.height + '%';
        wrapper.style.transform = 'translate(-50%, -50%)';
        wrapper.className = 'vt3d-media-item shadow-2xl';

        if (m.type === 'image') {
            const img = document.createElement('img');
            img.src = m.file_path;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'contain';
            wrapper.appendChild(img);
        } else if (m.type === 'video') {
            const vid = document.createElement('video');
            vid.src = m.file_path;
            vid.controls = true;
            vid.loop = true;
            vid.muted = false; // Allow unmute from controls
            vid.style.width = '100%';
            vid.style.height = '100%';
            vid.style.objectFit = 'contain';
            wrapper.appendChild(vid);
        }

        layer.appendChild(wrapper);
    });
}

function updateSceneTransform() {
    const scene = document.getElementById('vt3d-scene');
    if(scene) {
        scene.style.transform = `translateZ(${currentZoom}px) rotateX(${currentRotationX}deg) rotateY(${currentRotationY}deg)`;
    }
}

function setView(view) {
    currentView = view;
    
    if (view === 'front') currentRotationY = 0;
    else if (view === 'left') currentRotationY = 90;
    else if (view === 'back') currentRotationY = 180;
    else if (view === 'right') currentRotationY = -90;

    currentRotationX = 0; // Reset look up/down

    updateSceneTransform();

    // Update buttons UI
    document.querySelectorAll('.vt3d-view-btn').forEach(btn => {
        if (btn.dataset.view === view) {
            btn.classList.add('active');
            btn.style.backgroundColor = '#3b82f6';
            btn.style.color = 'white';
        } else {
            btn.classList.remove('active');
            btn.style.backgroundColor = '';
            btn.style.color = '';
        }
    });
}

window.handleDoorClick = function(e, doorEl) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    if (!currentRoom) return;
    if (window.isNavigatingToDoor) return;

    // If doorEl is not passed (legacy/fallback), try to find the active one
    if (!doorEl) {
        doorEl = document.querySelector('.vt3d-door-slot[style*="display: block"], .vt3d-door-slot[style*="display:block"]');
    }

    // Find the config for THIS specific door based on its wall
    const wall = doorEl ? doorEl.dataset.wall : 'back';
    const config = (currentRoom.doors && currentRoom.doors[wall]) ? currentRoom.doors[wall] : null;
    
    if (!config || config.link_type === 'none') return;
    
    window.isNavigatingToDoor = true;

    // 1. Play smooth door open animation without moving the user's camera
    if (doorEl) {
        doorEl.classList.add('vt3d-door-open');
    }

    // 2. Transition to new room or URL after the door fully opens
    setTimeout(() => {
        window.isNavigatingToDoor = false; // Reset lock
        if(doorEl) doorEl.classList.remove('vt3d-door-open'); // Close door behind us

        if (config.link_type === 'url' && config.target) {
            if(config.target.startsWith('http')) {
                window.open(config.target, '_blank');
            } else {
                window.location.href = config.target;
            }
        } else if (config.link_type === 'room' && config.target) {
            const targetRoomId = parseInt(config.target);
            if(!isNaN(targetRoomId)) {
                closeRoom3D();
                setTimeout(() => { openRoom3D(targetRoomId); }, 50);
            }
        }
    }, 1200); // match CSS door open animation duration
};
