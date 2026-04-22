<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app()->getLocale() === 'en' && $book->title_en ? $book->title_en : $book->title }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #f8fafc;
            font-family: 'Montserrat', sans-serif;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .viewer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            z-index: 10;
        }

        .viewer-back {
            text-decoration: none;
            color: #4b5563;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
        }

        .viewer-back:hover {
            color: #111827;
        }

        .viewer-title {
            font-weight: 600;
            color: #111827;
            text-align: center;
            flex: 2;
        }

        .viewer-spacer {
            flex: 1;
        }

        .viewer-content {
            flex: 1;
            padding: 1rem;
            position: relative;
            overflow: hidden; /* Prevent scrolling */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center; /* Center everything vertically */
            background: transparent;
        }

        /* Books Styles */
        .flip-book {
            margin: 0 auto;
            transition: transform 0.6s cubic-bezier(0.645, 0.045, 0.355, 1);
        }

        .flip-book.is-front-cover {
            transform: translateX(-25%);
        }
        .flip-book.is-back-cover {
            transform: translateX(25%);
        }

        .flip-book-wrapper {
            width: 100%;
            /* 550x733 ratio per page -> 1100x733 total = ~1.5 ratio */
            /* Scale width dynamically based on available height to ensure it fits without scrolling */
            max-width: min(1200px, calc((100vh - 200px) * 1.5));
            margin: 0 auto;
            display: block;
            position: relative;
            padding: 10px 0;
            box-sizing: border-box;
        }

        .page {
            padding: 20px;
            background-color: hsl(35, 55%, 98%);
            color: hsl(35, 35%, 35%);
            border: solid 1px hsl(35, 20%, 70%);
            overflow: hidden;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }

        .page .page-content { width: 100%; height: 100%; position: relative; }
        .page .page-content .page-header { position: absolute; top: 0; left: 0; right: 0; height: 30px; font-size: 100%; text-transform: uppercase; text-align: center; z-index: 1; }
        /* Contained mode (default): inner is bounded by header & footer */
        .page .page-content .page-inner { position: absolute; left: 15px; right: 0; top: 30px; bottom: 30px; }
        /* Fullbleed mode: inner spans the entire page; header/footer still rendered but image overlaps when 100% */
        .page.fullbleed-page { padding: 0; }
        .page .page-content.fullbleed .page-inner { left: 0; right: 0; top: 0; bottom: 0; z-index: 2; }
        .page .page-content.fullbleed .page-header { left: 20px; right: 20px; top: 8px; background: transparent; border: 0; }
        .page .page-content.fullbleed .page-footer { left: 20px; right: 20px; bottom: 8px; background: transparent; border: 0; }
        .page .page-content .page-image { position: absolute; background-size: contain; background-position: center center; background-repeat: no-repeat; }
        .page .page-content.fullbleed .page-image { background-size: 100% 100%; }
        .page .page-content .page-text { position: absolute; font-size: 80%; text-align: justify; padding: 8px; box-sizing: border-box; overflow: auto; z-index: 3; }
        .page .page-content .page-footer { position: absolute; bottom: 0; left: 0; right: 0; height: 30px; border-top: solid 1px hsl(35, 55%, 90%); font-size: 80%; color: hsl(35, 20%, 50%); z-index: 1; }
        .page.--left { border-right: 0; box-shadow: inset -7px 0 30px -7px rgba(0, 0, 0, 0.4); }
        .page.--right { border-left: 0; box-shadow: inset 7px 0 30px -7px rgba(0, 0, 0, 0.4); text-align: right; }
        .page.hard { background-color: hsl(35, 50%, 90%); border: solid 1px hsl(35, 20%, 50%); }
        .page.page-cover { background-color: transparent; color: hsl(35, 35%, 35%); border: solid 1px hsl(35, 20%, 50%); padding: 0; }
        .page.page-cover .page-cover-inner { position: absolute; inset: 0; background: linear-gradient(to bottom, #b45309, #78350f); overflow: hidden; }
        .page.page-cover .cover-spine { position: absolute; left: 0; top: 0; bottom: 0; width: 8px; background: linear-gradient(to right, #78350f, #b45309); }
        .page.page-cover .cover-image-container { position: absolute; top: 12px; left: 18px; right: 12px; bottom: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.1); overflow: hidden; }
        .page.page-cover .cover-image-container img { max-width: 100%; max-height: 100%; object-fit: contain; pointer-events: none; }
        .page.page-cover .cover-title { position: absolute; top: 16px; left: 0; right: 0; text-align: center; padding: 0 16px; color: white; font-weight: 600; font-size: 1.1rem; text-shadow: 0 2px 4px rgba(0,0,0,0.5); line-height: 1.3; z-index: 1; }
        .page.page-cover .cover-extra-texts { position: absolute; bottom: 16px; left: 0; right: 0; text-align: center; padding: 0 16px; z-index: 1; }
        .page.page-cover .cover-extra-texts span { display: block; color: rgba(255,255,255,0.8); font-size: 0.7rem; text-shadow: 0 1px 3px rgba(0,0,0,0.5); margin-top: 4px; }
        .page.page-cover h2 { text-align: center; padding-top: 50%; font-size: 210%; }
        .page.page-cover-top { box-shadow: inset 0px 0 30px 0px rgba(36, 10, 3, 0.5), -2px 0 5px 2px rgba(0, 0, 0, 0.4); }
        .page.page-cover-bottom { box-shadow: inset 0px 0 30px 0px rgba(36, 10, 3, 0.5), 10px 0 8px 0px rgba(0, 0, 0, 0.4); }

        .vb-controls { display: flex; align-items: center; justify-content: center; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem; padding-bottom: 0.5rem;}
        .vb-controls button { padding: 0.5rem 1.25rem; background: #0d9488; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 500; font-size: 0.9rem; transition: background 0.2s; }
        .vb-controls button:hover { background: #0f766e; }
        .vb-state-info { display: none; }
    </style>
</head>
<body>

    <div class="viewer-header">
        <a href="?" class="viewer-back">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            {{ app()->getLocale() === 'en' ? 'Back to Exhibition' : 'Kembali ke Pameran' }}
        </a>
        <div class="viewer-title">
            {{ app()->getLocale() === 'en' && $book->title_en ? $book->title_en : $book->title }}
        </div>
        <div class="viewer-spacer"></div>
    </div>

    <div class="viewer-content">
        @php
            $bookPages = $book->pages()->orderBy('order')->get();
            $bookId = 'flipBook_' . $book->id;
            $coverScaleFactor = 550 / 192;
        @endphp

        <div class="flip-book-wrapper">
            <div class="flip-book" id="{{ $bookId }}">
                {{-- Front Cover --}}
                @php
                    $titlePos = $book->title_position ?? [];
                    $titleTx = ($titlePos['x'] ?? 0) * $coverScaleFactor;
                    $titleTy = ($titlePos['y'] ?? 0) * $coverScaleFactor;
                    $coverTexts = $book->cover_texts ?? [];
                @endphp
                <div class="page page-cover page-cover-top" data-density="hard">
                    <div class="page-content">
                        <div class="page-cover-inner">
                            <div class="cover-spine"></div>
                            @if($book->cover_image)
                            <div class="cover-image-container">
                                <img src="{{ asset('storage/' . $book->cover_image) }}" style="transform: translate({{ ($book->cover_position['x'] ?? 0) * $coverScaleFactor }}px, {{ ($book->cover_position['y'] ?? 0) * $coverScaleFactor }}px) scale({{ $book->cover_scale ?? 1 }});">
                            </div>
                            @endif
                            <div class="cover-title" style="transform: translate({{ $titleTx }}px, {{ $titleTy }}px);">
                                {{ app()->getLocale() === 'en' && $book->title_en ? $book->title_en : $book->title }}
                            </div>
                            @if(count($coverTexts) > 0)
                            <div class="cover-extra-texts">
                                @foreach($coverTexts as $ct)
                                    @php
                                        $ctPos = $ct['position'] ?? [];
                                        $ctTx = ($ctPos['x'] ?? 0) * $coverScaleFactor;
                                        $ctTy = ($ctPos['y'] ?? 0) * $coverScaleFactor;
                                    @endphp
                                    <span style="transform: translate({{ $ctTx }}px, {{ $ctTy }}px);">{{ $ct['text'] ?? '' }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Pages --}}
                @foreach($bookPages as $pageIndex => $page)
                    @php
                        $images = $page->page_images ?? [];
                        $imagePositions = $page->image_positions ?? [];
                        $imageHeight = $page->image_height ?? 40;
                        $imgSize = max(20, (int) $imageHeight);
                        $fitMode = $page->image_fit_mode ?? 'contained';
                        $imgHeightPct = $fitMode === 'fullbleed' ? $imgSize : $imgSize * 0.75;
                        $textPos = $page->text_position ?? ['x' => 0, 'y' => 0, 'width' => 45, 'height' => 30];
                    @endphp
                    <div class="page {{ $fitMode === 'fullbleed' ? 'fullbleed-page' : '' }}">
                        <div class="page-content {{ $fitMode === 'fullbleed' ? 'fullbleed' : '' }}">
                            @if($page->title)
                            <h2 class="page-header">{{ app()->getLocale() === 'en' && $page->title_en ? $page->title_en : $page->title }}</h2>
                            @endif
                            <div class="page-inner">
                                @if(count($images) > 0)
                                    @foreach($images as $imgIndex => $image)
                                    @php $pos = $imagePositions[$imgIndex] ?? ['x' => 0, 'y' => 0]; @endphp
                                    <div class="page-image" style="background-image: url('{{ asset('storage/' . $image) }}'); left: {{ $pos['x'] ?? 0 }}%; top: {{ $pos['y'] ?? 0 }}%; width: {{ $imgSize }}%; height: {{ $imgHeightPct }}%;"></div>
                                    @endforeach
                                @endif
                                @if($page->content)
                                <div class="page-text" style="left: {{ $textPos['x'] ?? 0 }}%; top: {{ $textPos['y'] ?? 0 }}%; width: {{ $textPos['width'] ?? 45 }}%; height: {{ $textPos['height'] ?? 30 }}%;">
                                    {!! nl2br(e(app()->getLocale() === 'en' && $page->content_en ? $page->content_en : $page->content)) !!}
                                </div>
                                @endif
                            </div>
                            <div class="page-footer">{{ $pageIndex + 1 }}</div>
                        </div>
                    </div>
                @endforeach

                @if($bookPages->count() % 2 !== 0)
                    <div class="page"><div class="page-content"></div></div>
                @endif

                {{-- Back Cover --}}
                @php
                    $backTitlePos = $book->back_title_position ?? [];
                    $backTitleTx = ($backTitlePos['x'] ?? 0) * $coverScaleFactor;
                    $backTitleTy = ($backTitlePos['y'] ?? 0) * $coverScaleFactor;
                    $backCoverTexts = $book->back_cover_texts ?? [];
                @endphp
                <div class="page page-cover page-cover-bottom" data-density="hard">
                    <div class="page-content">
                        <div class="page-cover-inner">
                            <div class="cover-spine"></div>
                            @if($book->back_cover_image)
                            <div class="cover-image-container">
                                <img src="{{ asset('storage/' . $book->back_cover_image) }}" style="transform: translate({{ ($book->back_cover_position['x'] ?? 0) * $coverScaleFactor }}px, {{ ($book->back_cover_position['y'] ?? 0) * $coverScaleFactor }}px) scale({{ $book->back_cover_scale ?? 1 }});">
                            </div>
                            @endif
                            @if($book->back_title)
                            <div class="cover-title" style="transform: translate({{ $backTitleTx }}px, {{ $backTitleTy }}px);">
                                {{ $book->back_title }}
                            </div>
                            @endif
                            @if(count($backCoverTexts) > 0)
                            <div class="cover-extra-texts">
                                @foreach($backCoverTexts as $bct)
                                    @php
                                        $bctPos = $bct['position'] ?? [];
                                        $bctTx = ($bctPos['x'] ?? 0) * $coverScaleFactor;
                                        $bctTy = ($bctPos['y'] ?? 0) * $coverScaleFactor;
                                    @endphp
                                    <span style="transform: translate({{ $bctTx }}px, {{ $bctTy }}px);">{{ $bct['text'] ?? '' }}</span>
                                @endforeach
                            </div>
                            @endif
                            @if(!$book->back_cover_image && !$book->back_title)
                            <h2 style="color: white;">THE END</h2>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="vb-controls" data-book="{{ $bookId }}">
            <button type="button" class="btn-prev">{{ app()->getLocale() === 'en' ? 'Previous page' : 'Halaman sebelumnya' }}</button>
            <span class="page-info" style="color:#4b5563">[<span class="page-current">1</span> {{ app()->getLocale() === 'en' ? 'of' : 'dari' }} <span class="page-total">-</span>]</span>
            <button type="button" class="btn-next">{{ app()->getLocale() === 'en' ? 'Next page' : 'Halaman selanjutnya' }}</button>
        </div>
        <div class="vb-state-info" data-book="{{ $bookId }}">
            State: <i class="page-state">read</i>, orientation: <i class="page-orientation">landscape</i>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var bookId = '{{ $bookId }}';
            var bookEl = document.getElementById(bookId);

            function initBook() {
                if (typeof window.PageFlip === 'undefined') {
                    // Poll until PageFlip is loaded by Vite
                    setTimeout(initBook, 100);
                    return;
                }

                var pages = bookEl.querySelectorAll('.page');
                if (pages.length === 0) return;

                var pageFlip = new window.PageFlip(bookEl, {
                    width: 550,
                    height: 733,
                    size: "stretch", // Stretch ensures book matches container's width, which we cap dynamically
                    minWidth: 315,
                    maxWidth: 1000,
                    minHeight: 420,
                    maxHeight: 1350,
                    maxShadowOpacity: 0.5,
                    showCover: true,
                    mobileScrollSupport: false
                });

                pageFlip.loadFromHTML(pages);

                var totalPages = pageFlip.getPageCount();
                var currentPageIndex = 0;

                function updateCoverMode() {
                    var isFrontCover = (currentPageIndex === 0);
                    var isBackCover = (currentPageIndex >= totalPages - 1);
                    var orientation = pageFlip.getOrientation ? pageFlip.getOrientation() : 'landscape';

                    bookEl.classList.remove('is-front-cover', 'is-back-cover');
                    if (orientation === 'landscape') {
                        if (isFrontCover) bookEl.classList.add('is-front-cover');
                        else if (isBackCover) bookEl.classList.add('is-back-cover');
                    }
                }
                updateCoverMode();

                var controls = document.querySelector('.vb-controls');
                var stateInfo = document.querySelector('.vb-state-info');

                if (controls) {
                    controls.querySelector('.page-total').innerText = pageFlip.getPageCount();
                    controls.querySelector('.btn-prev').addEventListener('click', function() { pageFlip.flipPrev(); });
                    controls.querySelector('.btn-next').addEventListener('click', function() { pageFlip.flipNext(); });
                    pageFlip.on('flip', function(e) {
                        currentPageIndex = e.data;
                        controls.querySelector('.page-current').innerText = e.data + 1;
                        updateCoverMode();
                    });
                }

                if (stateInfo) {
                    stateInfo.querySelector('.page-orientation').innerText = pageFlip.getOrientation();
                    pageFlip.on('changeState', function(e) { stateInfo.querySelector('.page-state').innerText = e.data; });
                    pageFlip.on('changeOrientation', function(e) {
                        stateInfo.querySelector('.page-orientation').innerText = e.data;
                        updateCoverMode();
                    });
                }
            }

            // Start initialization loop
            initBook();
        });
    </script>
    {{-- Login required modal --}}
    @if(isset($requiresLoginModal) && $requiresLoginModal)
        @include('partials.login_modal', [
            'previewImage' => $loginModalPreview ?? null,
            'roomName' => $loginModalRoomName ?? null
        ])
    @endif
</body>
</html>
