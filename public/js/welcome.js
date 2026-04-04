document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

const youtubeCarousel = document.getElementById('youtube-carousel');
const youtubeDots = Array.from(document.querySelectorAll('.youtube-dot'));
let youtubeIsAnimating = false;
const youtubeAnimDuration = 420;

function loopIndex(index, total) {
    return (index + total) % total;
}

function removeAutoplayParam(src) {
    if (!src) return src;
    try {
        const parsed = new URL(src, window.location.origin);
        parsed.searchParams.delete('autoplay');
        return parsed.toString();
    } catch {
        return src
            .replace(/[?&]autoplay=1/g, '')
            .replace('?&', '?')
            .replace(/[?&]$/, '');
    }
}

function getYoutubeItems() {
    return youtubeCarousel ? Array.from(youtubeCarousel.querySelectorAll('.youtube-item')) : [];
}

function updateYoutubeClasses() {
    const items = getYoutubeItems();
    if (!items.length) return;

    // For fewer than 5 items, use the middle item as active
    const centerIdx = items.length >= 5 ? 2 : Math.floor(items.length / 2);

    items.forEach((item, idx) => {
        item.classList.remove('active', 'prev', 'next');
        if (idx === centerIdx) {
            item.classList.add('active');
        } else if (idx === centerIdx - 1) {
            item.classList.add('prev');
        } else if (idx === centerIdx + 1) {
            item.classList.add('next');
        }
        // Reset play button when item leaves active position
        const iframe = item.querySelector('iframe');
        const playBtn = item.querySelector('.youtube-play');
        if (iframe && playBtn) {
            const src = iframe.getAttribute('src');
            if (src && !src.includes('autoplay=1')) {
                playBtn.classList.remove('playing');
            }
        }
    });

    const center = items[centerIdx];
    const centerIndex = center ? Number(center.dataset.videoIndex) : 0;
    youtubeDots.forEach((dot, idx) => {
        dot.classList.toggle('active', idx === centerIndex);
    });
}

function initYoutubeItems() {
    const items = getYoutubeItems();
    items.forEach((item, idx) => {
        item.dataset.videoIndex = String(idx);
        const iframe = item.querySelector('iframe');
        if (iframe) {
            iframe.setAttribute('src', removeAutoplayParam(iframe.getAttribute('src')));
        }
        const playBtn = item.querySelector('.youtube-play');
        if (playBtn) playBtn.classList.remove('playing');
    });

    // For fewer than 5 items, enlarge active item and hide nav
    if (items.length < 5) {
        if (youtubeCarousel) youtubeCarousel.classList.add('youtube-few-items');
        const nav = document.querySelector('.youtube-nav');
        if (nav) nav.style.display = 'none';
    }
}

function animateYoutubeStep(direction) {
    if (!youtubeCarousel || youtubeIsAnimating) return;
    const itemsBefore = getYoutubeItems();
    if (itemsBefore.length < 5) return;

    youtubeIsAnimating = true;

    const firstRects = new Map(
        itemsBefore.map((item) => [item.dataset.videoIndex, item.getBoundingClientRect()])
    );

    if (direction > 0) {
        const first = itemsBefore[0];
        if (first) youtubeCarousel.appendChild(first);
    } else {
        const last = itemsBefore[itemsBefore.length - 1];
        if (last) youtubeCarousel.insertBefore(last, itemsBefore[0]);
    }

    updateYoutubeClasses();

    const itemsAfter = getYoutubeItems();
    itemsAfter.forEach((item) => {
        const thumb = item.querySelector('.youtube-thumb');
        if (!thumb) return;

        const firstRect = firstRects.get(item.dataset.videoIndex);
        const lastRect = item.getBoundingClientRect();
        const deltaX = firstRect ? firstRect.left - lastRect.left : 0;

        thumb.style.transition = 'none';
        thumb.style.transform = `translateX(${deltaX}px)`;
    });

    requestAnimationFrame(() => {
        itemsAfter.forEach((item) => {
            const thumb = item.querySelector('.youtube-thumb');
            if (!thumb) return;
            thumb.style.transition = `transform ${youtubeAnimDuration}ms cubic-bezier(0.22, 1, 0.36, 1)`;
            thumb.style.transform = 'translateX(0)';
        });

        window.setTimeout(() => {
            itemsAfter.forEach((item) => {
                const thumb = item.querySelector('.youtube-thumb');
                if (!thumb) return;
                thumb.style.transition = '';
                thumb.style.transform = '';
            });
            youtubeIsAnimating = false;
        }, youtubeAnimDuration);
    });
}

function goYoutube(direction) {
    animateYoutubeStep(direction);
}

const prevBtn = document.getElementById('youtube-prev');
const nextBtn = document.getElementById('youtube-next');

if (prevBtn) prevBtn.addEventListener('click', () => goYoutube(1));
if (nextBtn) nextBtn.addEventListener('click', () => goYoutube(-1));

youtubeDots.forEach((dot, idx) => {
    dot.addEventListener('click', () => {
        const items = getYoutubeItems();
        if (!items.length || youtubeIsAnimating) return;

        const centerIdx = items.length >= 5 ? 2 : Math.floor(items.length / 2);
        const currentCenter = Number(items[centerIdx].dataset.videoIndex);
        const total = items.length;
        const forward = loopIndex(idx - currentCenter, total);
        const backward = loopIndex(currentCenter - idx, total);

        if (forward === 0) return;

        const direction = forward <= backward ? 1 : -1;
        const steps = Math.min(forward, backward);

        let step = 0;
        const run = () => {
            if (step >= steps) return;
            if (youtubeIsAnimating) {
                window.setTimeout(run, 30);
                return;
            }
            animateYoutubeStep(direction);
            step += 1;
            window.setTimeout(run, youtubeAnimDuration + 20);
        };
        run();
    });
});

// Tombol play overlay - aktif di item active
document.querySelectorAll('.youtube-play').forEach((btn) => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const item = btn.closest('.youtube-item');
        if (item && item.classList.contains('active')) {
            const iframe = item.querySelector('iframe');
            if (iframe) {
                const src = iframe.getAttribute('src');
                if (src && !src.includes('autoplay=1')) {
                    const autoplaySrc = src + (src.includes('?') ? '&' : '?') + 'autoplay=1';
                    iframe.setAttribute('src', autoplaySrc);
                    btn.classList.add('playing');
                }
            }
        }
    });
});

// Initialize carousel
initYoutubeItems();
updateYoutubeClasses();
