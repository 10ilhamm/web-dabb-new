{{--
    Partial: Login required modal for protected public pages (/pameran/virtual/*).
    Variables expected from controller:
      $feature            – current Feature model
      $loginModalPreview  – URL of first room thumbnail (nullable)
      $loginModalRoomName – name of the first room (nullable)
--}}
<div id="loginRequiredModal"
     style="position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;padding:1rem;"
     aria-modal="true" role="dialog">

    {{-- Backdrop --}}
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);"></div>

    {{-- Card — compact size --}}
    <div style="position:relative;background:#fff;border-radius:1.5rem;width:100%;max-width:720px;
                display:flex;overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,0.3);min-height:400px;">

        {{-- ─── Left: Login Form ────────────────────────────────── --}}
        <div style="flex:1;padding:2.25rem 2.5rem;display:flex;flex-direction:column;justify-content:center;min-width:0;">

            <h2 style="color:#6c757d;font-size:1.15rem;font-weight:800;margin:0 0 0.75rem;">{{ __('auth.welcome') }}</h2>
            <h3 style="color:#212529;font-size:1.05rem;font-weight:700;margin:0 0 0.25rem;">{{ __('auth.login') }}</h3>
            <p style="color:#6c757d;font-size:0.8rem;margin:0 0 1.1rem;">{{ __('auth.login_subtitle') }}</p>

            @if(session('status'))
                <div style="padding:8px 12px;background:#d1fae5;border:1px solid #6ee7b7;border-radius:6px;font-size:12px;color:#065f46;margin-bottom:0.75rem;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="redirect" value="{{ url()->current() }}">

                {{-- Email --}}
                <div style="margin-bottom:0.85rem;">
                    <label for="lrm_email" style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.3rem;">{{ __('Email') }}</label>
                    <input id="lrm_email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:7px;font-size:0.85rem;box-sizing:border-box;font-family:inherit;transition:border-color 0.2s,box-shadow 0.2s;outline:none;"
                           onfocus="this.style.borderColor='#0579cb';this.style.boxShadow='0 0 0 3px rgba(5,121,203,0.12)';"
                           onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none';">
                    @error('email')<p style="color:#dc2626;font-size:11px;margin:3px 0 0;">{{ $message }}</p>@enderror
                </div>

                {{-- Password --}}
                <div style="margin-bottom:0.6rem;">
                    <label for="lrm_password" style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.3rem;">{{ __('Password') }}</label>
                    <input id="lrm_password" type="password" name="password" required autocomplete="current-password"
                           style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:7px;font-size:0.85rem;box-sizing:border-box;font-family:inherit;transition:border-color 0.2s,box-shadow 0.2s;outline:none;"
                           onfocus="this.style.borderColor='#0579cb';this.style.boxShadow='0 0 0 3px rgba(5,121,203,0.12)';"
                           onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none';">
                    @error('password')<p style="color:#dc2626;font-size:11px;margin:3px 0 0;">{{ $message }}</p>@enderror
                </div>

                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="color:#3598db;font-size:12px;text-decoration:none;display:inline-block;margin-bottom:1rem;">
                        {{ __('auth.forgot_password') }}
                    </a>
                @endif

                {{-- Submit --}}
                <button type="submit"
                        style="display:block;width:100%;background:#0579cb;color:#fff;border:none;padding:10px;border-radius:7px;font-size:14px;font-weight:600;cursor:pointer;margin-bottom:1rem;box-shadow:0 3px 8px rgba(5,121,203,0.3);transition:background 0.2s,transform 0.15s;"
                        onmouseover="this.style.background='#0466ac';this.style.transform='translateY(-1px)';"
                        onmouseout="this.style.background='#0579cb';this.style.transform='none';">
                    {{ __('auth.submit') }}
                </button>

                {{-- Divider --}}
                <div style="display:flex;align-items:center;gap:0.5rem;color:#9ca3af;font-size:12px;margin-bottom:1rem;">
                    <span style="flex:1;height:1px;background:#e5e7eb;"></span>{{ __('auth.or') }}<span style="flex:1;height:1px;background:#e5e7eb;"></span>
                </div>

                {{-- Google --}}
                <a href="#"
                   style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;background:#fff;color:#374151;border:1px solid #d1d5db;padding:9px;border-radius:7px;font-size:13px;font-weight:600;text-decoration:none;transition:background 0.2s;box-sizing:border-box;"
                   onmouseover="this.style.background='#f9fafb';" onmouseout="this.style.background='#fff';">
                    <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" style="height:18px;" alt="G">
                    {{ __('auth.login_google') }}
                </a>

                <p style="text-align:center;margin-top:0.85rem;font-size:12px;color:#6b7280;">
                    {{ __('auth.no_account') }}
                    <a href="{{ route('register') }}" style="color:#3598db;font-weight:600;text-decoration:none;">{{ __('auth.register_link') }}</a>
                </p>
            </form>
        </div>

        {{-- ─── Right: Dynamic preview ──────────────────────────── --}}
        <div style="flex:1;position:relative;overflow:hidden;min-height:400px;display:flex;flex-direction:column;">

            {{-- Background: first room thumbnail or fallback --}}
            @if(!empty($loginModalPreview))
                <div style="position:absolute;inset:0;background:url('{{ $loginModalPreview }}') center/cover no-repeat;"></div>
            @else
                <div style="position:absolute;inset:0;background:url('{{ asset('image/background_login.png') }}') center/cover no-repeat;"></div>
            @endif

            {{-- Dark gradient overlay --}}
            <div style="position:absolute;inset:0;background:linear-gradient(to bottom, rgba(15,42,92,0.55) 0%, rgba(15,42,92,0.75) 100%);"></div>

            {{-- Content --}}
            <div style="position:relative;z-index:2;padding:1.75rem;display:flex;flex-direction:column;height:100%;justify-content:space-between;">

                {{-- Top: Logo + site name --}}
                <div style="display:flex;align-items:center;gap:10px;color:#fff;">
                    <img src="{{ asset('image/logo_anri.png') }}" alt="ANRI" style="height:36px;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.4));">
                    <span style="font-size:0.85rem;font-weight:700;text-shadow:0 1px 3px rgba(0,0,0,0.5);line-height:1.25;">{!! __('auth.banner_title') !!}</span>
                </div>

                {{-- Bottom: Feature & room info --}}
                <div style="color:#fff;">
                    <div style="display:inline-block;background:rgba(255,255,255,0.15);backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,0.25);border-radius:999px;font-size:11px;font-weight:600;padding:4px 12px;margin-bottom:0.5rem;letter-spacing:0.04em;text-transform:uppercase;">
                        {{ $feature->parent?->name ?? 'Pameran Arsip' }}
                    </div>
                    <h4 style="font-size:1.15rem;font-weight:800;margin:0 0 0.3rem;text-shadow:0 2px 4px rgba(0,0,0,0.5);">
                        {{ $feature->name }}
                    </h4>
                    @if(!empty($loginModalRoomName))
                        <p style="font-size:0.8rem;opacity:0.8;margin:0;display:flex;align-items:center;gap:5px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.361a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                            Preview: {{ $loginModalRoomName }}
                        </p>
                    @else
                        <p style="font-size:0.8rem;opacity:0.8;margin:0;">Masuk untuk menjelajahi ruangan virtual 360°</p>
                    @endif
                </div>

            </div>
        </div>

    </div>
</div>
