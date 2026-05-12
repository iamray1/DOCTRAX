@php
    $previewTitle = $title ?? 'DepEd DOCTRAX | Document Tracking System';
    $previewDescription = $description ?? 'Submit and track documents online through the official DepEd City of San Jose del Monte Document Tracking System.';
    $previewImage = $image ?? asset('images/landingpage.svg');
    $previewUrl = $url ?? url()->current();
    $previewImagePath = parse_url($previewImage, PHP_URL_PATH) ?: $previewImage;
    $previewImageType = $imageType ?? (strtolower(pathinfo($previewImagePath, PATHINFO_EXTENSION)) === 'svg' ? 'image/svg+xml' : 'image/png');
    $previewImageWidth = $imageWidth ?? 1200;
    $previewImageHeight = $imageHeight ?? 630;
@endphp
<meta name="description" content="{{ $previewDescription }}">
<link rel="canonical" href="{{ $previewUrl }}">
<meta property="og:site_name" content="DepEd DOCTRAX">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $previewTitle }}">
<meta property="og:description" content="{{ $previewDescription }}">
<meta property="og:url" content="{{ $previewUrl }}">
<meta property="og:image" content="{{ $previewImage }}">
<meta property="og:image:secure_url" content="{{ $previewImage }}">
<meta property="og:image:type" content="{{ $previewImageType }}">
<meta property="og:image:width" content="{{ $previewImageWidth }}">
<meta property="og:image:height" content="{{ $previewImageHeight }}">
<meta property="og:image:alt" content="DepEd DOCTRAX Document Tracking System logo">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $previewTitle }}">
<meta name="twitter:description" content="{{ $previewDescription }}">
<meta name="twitter:image" content="{{ $previewImage }}">
