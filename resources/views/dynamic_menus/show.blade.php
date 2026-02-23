@extends('layouts.admin')

@section('title', $menu->name)

@section('content')

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">{{ $menu->name }}</h2>
            @if($title)

                <p class="text-muted mb-0">{{ $title }}</p>
            @endif
        </div>
    </div>
    <div class="fade-in-up ">

        <div class="card shadow-sm border-0 rounded-4 flex-grow-1 overflow-hidden" style="min-height: 70vh;">
            <div class="card-body p-0 d-flex flex-column">
                @if($menu->spreadsheet_id)
                    @php
                        $embedUrl = "https://docs.google.com/spreadsheets/d/{$menu->spreadsheet_id}/htmlembed";
                        $queryParams = [];

                        if ($menu->sheet_mode === 'single' && $menu->gid !== null) {
                            $queryParams[] = "gid={$menu->gid}";
                            $queryParams[] = "single=true";
                        } else {
                            $queryParams[] = "widget=true";
                            $queryParams[] = "headers=false";
                        }

                        if (!empty($queryParams)) {
                            $embedUrl .= "?" . implode("&", $queryParams);
                        }
                    @endphp
                    <iframe src="{{ $embedUrl }}" width="100%" height="100%" style="border:0; flex-grow: 1;"
                        title="{{ $menu->name }}"></iframe>
                @else
                    <div class="d-flex flex-grow-1 justify-content-center align-items-center text-muted">
                        <div class="text-center">
                            <i class="fas fa-file-excel fa-4x mb-3 opacity-50"></i>
                            <h5>Data Spreadsheet Tidak Ditemukan</h5>
                            <p>Administrator belum menyetel ID Spreadsheet untuk menu ini.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection