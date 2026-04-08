@once
    <style>
        .pg-shared {
            width: 100%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            padding: 18px 22px;
            border-top: 1px solid #f1f5f9;
            background: linear-gradient(180deg, #fff 0%, #f8fbff 100%);
        }
        .pg-shared__meta {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            min-width: 0;
        }
        .pg-shared__page {
            display: inline-flex;
            align-items: baseline;
            gap: 5px;
            white-space: nowrap;
            font-size: 12px;
            line-height: 1;
        }
        .pg-shared__page-label {
            color: #64748b;
            font-weight: 600;
        }
        .pg-shared__page-value {
            color: #1b263b;
            font-weight: 700;
            letter-spacing: .01em;
        }
        .pg-shared__page-sep {
            color: #94a3b8;
            font-weight: 700;
        }
        .pg-shared__summary {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }
        .pg-shared__summary strong {
            color: #1b263b;
        }
        .pg-shared__actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }
        .pg-shared__links {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            flex-wrap: wrap;
        }
        .pg-shared__link,
        .pg-shared__current,
        .pg-shared__disabled,
        .pg-shared__dots {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: all .18s ease;
            font-family: Poppins, sans-serif;
        }
        .pg-shared__link {
            border: 1px solid #dbe5f0;
            color: #334155;
            background: #fff;
        }
        .pg-shared__link:hover {
            border-color: #004494;
            background: #004494;
            color: #fff;
        }
        .pg-shared__current {
            border: 1px solid transparent;
            background: linear-gradient(135deg, #0f61c9 0%, #0056b3 100%);
            color: #fff;
        }
        .pg-shared__disabled {
            border: 1px dashed #dbe3ee;
            background: #f8fafc;
            color: #cbd5e1;
            cursor: not-allowed;
        }
        .pg-shared__dots {
            min-width: auto;
            height: 40px;
            padding: 0 2px;
            border: none;
            background: none;
            color: #94a3b8;
        }
        .pg-shared__nav {
            gap: 7px;
            padding: 0 14px;
        }
        .pg-shared__nav i {
            font-size: 10px;
        }
        .pg-shared__jump {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 8px 6px 10px;
            border: 1px solid #dbe5f0;
            border-radius: 12px;
            background: #fff;
        }
        .pg-shared__jump label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            white-space: nowrap;
        }
        .pg-shared__jump input {
            width: 58px;
            height: 36px;
            padding: 0 10px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            color: #1b263b;
            font-family: Poppins, sans-serif;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            outline: none;
        }
        .pg-shared__jump input:focus {
            border-color: #0056b3;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0, 86, 179, .08);
        }
        .pg-shared__jump button {
            height: 36px;
            padding: 0 12px;
            border: none;
            border-radius: 10px;
            background: #0056b3;
            color: #fff;
            font-family: Poppins, sans-serif;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }
        .pg-shared__jump button:hover {
            background: #004494;
        }
        @media (max-width: 900px) {
            .pg-shared {
                justify-content: center;
                padding: 14px;
            }
            .pg-shared__meta {
                justify-content: center;
                text-align: center;
            }
            .pg-shared__actions {
                justify-content: center;
                width: 100%;
            }
            .pg-shared__links {
                justify-content: center;
            }
            .pg-shared__jump {
                justify-content: center;
            }
        }
        @media (max-width: 420px) {
            .pg-shared__link,
            .pg-shared__current,
            .pg-shared__disabled {
                min-width: 36px;
                height: 36px;
                padding: 0 10px;
                border-radius: 10px;
            }
            .pg-shared__nav-label {
                display: none;
            }
            .pg-shared__jump {
                width: 100%;
            }
            .pg-shared__jump input {
                flex: 1;
                max-width: 72px;
            }
        }
        @media print {
            .pg-shared {
                display: none !important;
            }
        }
    </style>
@endonce

@php
    $paginator = $paginator ?? null;
    $itemLabel = $itemLabel ?? 'items';
    $jumpLabel = $jumpLabel ?? 'Jump to';
    $pageName = $pageName ?? ($paginator ? $paginator->getPageName() : 'page');
    $jumpAction = $jumpAction ?? url()->current();
    $jumpId = $jumpId ?? ('pg_jump_' . preg_replace('/[^a-z0-9_]+/i', '_', $pageName));
    $currentPage = $paginator ? (int) $paginator->currentPage() : 1;
    $lastPage = $paginator ? max(1, (int) $paginator->lastPage()) : 1;
    $queryInputs = request()->except($pageName);
    $windowStart = max(1, $currentPage - 1);
    $windowEnd = min($lastPage, $currentPage + 1);
@endphp

@if($paginator && $paginator->hasPages())
    <div class="pg-shared">
        <div class="pg-shared__meta">
            <span class="pg-shared__page" aria-label="Current page">
                <span class="pg-shared__page-label">Page</span>
                <span class="pg-shared__page-value">{{ $currentPage }}</span>
                <span class="pg-shared__page-sep">/</span>
                <span class="pg-shared__page-value">{{ $lastPage }}</span>
            </span>
            @if($paginator->firstItem() !== null)
                <span class="pg-shared__summary">
                    Showing <strong>{{ $paginator->firstItem() }}</strong>-<strong>{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong> {{ $itemLabel }}
                </span>
            @endif
        </div>

        <div class="pg-shared__actions">
            <div class="pg-shared__links" aria-label="Pagination">
                @if($paginator->onFirstPage())
                    <span class="pg-shared__disabled pg-shared__nav" aria-disabled="true">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        <span class="pg-shared__nav-label">Prev</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="pg-shared__link pg-shared__nav" aria-label="Go to previous page">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        <span class="pg-shared__nav-label">Prev</span>
                    </a>
                @endif

                @if($currentPage > 2)
                    <a href="{{ $paginator->url(1) }}" class="pg-shared__link" aria-label="Go to page 1">1</a>
                @endif
                @if($currentPage > 3)
                    <span class="pg-shared__dots" aria-hidden="true">...</span>
                @endif

                @foreach($paginator->getUrlRange($windowStart, $windowEnd) as $page => $url)
                    @if($page == $currentPage)
                        <span class="pg-shared__current" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pg-shared__link" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($currentPage < $lastPage - 2)
                    <span class="pg-shared__dots" aria-hidden="true">...</span>
                @endif
                @if($currentPage < $lastPage - 1)
                    <a href="{{ $paginator->url($lastPage) }}" class="pg-shared__link" aria-label="Go to page {{ $lastPage }}">{{ $lastPage }}</a>
                @endif

                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="pg-shared__link pg-shared__nav" aria-label="Go to next page">
                        <span class="pg-shared__nav-label">Next</span>
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </a>
                @else
                    <span class="pg-shared__disabled pg-shared__nav" aria-disabled="true">
                        <span class="pg-shared__nav-label">Next</span>
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </span>
                @endif
            </div>

            <form method="GET" action="{{ $jumpAction }}" class="pg-shared__jump" onsubmit="var p=this.querySelector('[name={{ json_encode($pageName) }}]');if(p){var v=parseInt(p.value||'1',10);if(!isFinite(v))v=1;p.value=Math.min(Math.max(v,1),{{ $lastPage }});}">
                @foreach($queryInputs as $name => $value)
                    @if(is_array($value))
                        @foreach($value as $nestedName => $nestedValue)
                            @if(!is_array($nestedValue))
                                <input type="hidden" name="{{ $name }}[{{ $nestedName }}]" value="{{ $nestedValue }}">
                            @endif
                        @endforeach
                    @elseif($value !== null)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                @endforeach
                <label for="{{ $jumpId }}">{{ $jumpLabel }}</label>
                <input type="number" id="{{ $jumpId }}" name="{{ $pageName }}" min="1" max="{{ $lastPage }}" value="{{ $currentPage }}" inputmode="numeric" aria-label="{{ $jumpLabel }} page">
                <button type="submit">Go</button>
            </form>
        </div>
    </div>
@endif
