/**
 * Request utilities: debounce, rate limiting, network fallbacks, skeleton loading.
 * Loaded globally via <script src="/js/request-utils.js" defer>.
 * Works with spa.js and survives SPA swaps via event delegation.
 */
(function () {
    'use strict';

    // Debounce
    window.debounce = function (fn, delay, immediate) {
        var timer = null;
        return function () {
            var ctx = this;
            var args = arguments;
            var callNow = immediate && !timer;

            clearTimeout(timer);
            timer = setTimeout(function () {
                timer = null;
                if (!immediate) fn.apply(ctx, args);
            }, delay);

            if (callNow) fn.apply(ctx, args);
        };
    };

    // Visibility-aware polling
    window.smartInterval = function (fn, delay) {
        var id = setInterval(fn, delay);

        function onVisibilityChange() {
            if (document.hidden) {
                if (id) {
                    clearInterval(id);
                    id = null;
                }
                return;
            }

            fn();
            if (!id) id = setInterval(fn, delay);
        }

        document.addEventListener('visibilitychange', onVisibilityChange);

        return {
            clear: function () {
                if (id) {
                    clearInterval(id);
                    id = null;
                }
                document.removeEventListener('visibilitychange', onVisibilityChange);
            }
        };
    };

    // Input sanitization
    window.sanitizeInput = function (str) {
        if (typeof str !== 'string') return '';
        return str.replace(/<[^>]*>/g, '').trim();
    };

    window.escapeHtml = function (str) {
        if (typeof str !== 'string') return '';
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return str.replace(/[&<>"']/g, function (c) { return map[c]; });
    };

    // Compact counters
    window.formatCompactCount = function (value) {
        if (value === null || value === undefined || value === '') return '0';

        var normalized = typeof value === 'string' ? value.replace(/,/g, '').trim() : value;
        var num = Number(normalized);
        if (!isFinite(num)) return String(value);

        var abs = Math.abs(num);
        if (abs < 1000) return String(Math.round(num));

        var units = ['K', 'M', 'B', 'T'];
        var unitIndex = -1;

        while (abs >= 1000 && unitIndex < units.length - 1) {
            num = num / 1000;
            abs = abs / 1000;
            unitIndex++;
        }

        var rounded = Number(num.toFixed(1));
        if (Math.abs(rounded) >= 1000 && unitIndex < units.length - 1) {
            num = rounded / 1000;
            unitIndex++;
        }

        return num.toFixed(1).replace(/\.0$/, '') + units[unitIndex];
    };

    function normalizeReceiptText(value, fallback) {
        var text = String(value === null || value === undefined ? '' : value)
            .replace(/\s+/g, ' ')
            .trim();
        return text || (fallback || '');
    }

    function sanitizeReceiptFilePart(value) {
        var cleaned = normalizeReceiptText(value, 'receipt')
            .replace(/[^A-Za-z0-9._-]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        return cleaned || 'receipt';
    }

    function waitForImageElement(img) {
        return new Promise(function (resolve, reject) {
            if (!img) {
                reject(new Error('QR code is not available yet.'));
                return;
            }

            if (img.complete && img.naturalWidth > 0) {
                resolve(img);
                return;
            }

            var settled = false;

            function cleanup() {
                img.removeEventListener('load', onLoad);
                img.removeEventListener('error', onError);
            }

            function onLoad() {
                if (settled) return;
                settled = true;
                cleanup();
                resolve(img);
            }

            function onError() {
                if (settled) return;
                settled = true;
                cleanup();
                reject(new Error('QR code could not be loaded yet.'));
            }

            img.addEventListener('load', onLoad, { once: true });
            img.addEventListener('error', onError, { once: true });
        });
    }

    function loadReceiptImage(src) {
        return new Promise(function (resolve, reject) {
            if (!src) {
                reject(new Error('QR code is not available yet.'));
                return;
            }

            var image = new Image();
            image.onload = function () { resolve(image); };
            image.onerror = function () { reject(new Error('QR code image could not be prepared.')); };
            image.src = src;
        });
    }

    function splitReceiptWord(ctx, word, maxWidth) {
        if (!word) return [''];
        if (ctx.measureText(word).width <= maxWidth) return [word];

        var parts = [];
        var current = '';

        for (var i = 0; i < word.length; i++) {
            var next = current + word.charAt(i);
            if (current && ctx.measureText(next).width > maxWidth) {
                parts.push(current);
                current = word.charAt(i);
            } else {
                current = next;
            }
        }

        if (current) parts.push(current);
        return parts.length ? parts : [word];
    }

    function wrapReceiptCanvasText(ctx, text, maxWidth) {
        var content = String(text === null || text === undefined ? '' : text)
            .replace(/\r/g, '')
            .trim();
        if (!content) return ['-'];

        var paragraphs = content.split('\n');
        var lines = [];

        paragraphs.forEach(function (paragraph) {
            var words = paragraph.trim().split(/\s+/).filter(Boolean);
            if (!words.length) {
                lines.push('');
                return;
            }

            var line = '';

            words.forEach(function (word) {
                var segments = splitReceiptWord(ctx, word, maxWidth);

                segments.forEach(function (segment) {
                    var testLine = line ? line + ' ' + segment : segment;
                    if (!line || ctx.measureText(testLine).width <= maxWidth) {
                        line = testLine;
                        return;
                    }

                    lines.push(line);
                    line = segment;
                });
            });

            if (line) lines.push(line);
        });

        return lines.length ? lines : ['-'];
    }

    function drawReceiptRoundedRect(ctx, x, y, width, height, radius, fillColor, strokeColor, lineWidth) {
        var safeRadius = Math.max(0, Math.min(radius, width / 2, height / 2));
        ctx.beginPath();
        ctx.moveTo(x + safeRadius, y);
        ctx.lineTo(x + width - safeRadius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + safeRadius);
        ctx.lineTo(x + width, y + height - safeRadius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - safeRadius, y + height);
        ctx.lineTo(x + safeRadius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - safeRadius);
        ctx.lineTo(x, y + safeRadius);
        ctx.quadraticCurveTo(x, y, x + safeRadius, y);
        ctx.closePath();

        if (fillColor) {
            ctx.fillStyle = fillColor;
            ctx.fill();
        }

        if (strokeColor) {
            ctx.lineWidth = lineWidth || 1;
            ctx.strokeStyle = strokeColor;
            ctx.stroke();
        }
    }

    function drawReceiptLines(ctx, lines, x, y, lineHeight) {
        lines.forEach(function (line, index) {
            ctx.fillText(line, x, y + (index * lineHeight));
        });
    }

    function measureReceiptSpacedText(ctx, text, spacing) {
        var value = String(text === null || text === undefined ? '' : text);
        if (!value) return 0;

        var totalWidth = 0;
        for (var i = 0; i < value.length; i++) {
            totalWidth += ctx.measureText(value.charAt(i)).width;
            if (i < value.length - 1) totalWidth += spacing;
        }

        return totalWidth;
    }

    function drawReceiptSpacedText(ctx, text, x, y, spacing, align) {
        var value = String(text === null || text === undefined ? '' : text);
        if (!value) return 0;

        var totalWidth = measureReceiptSpacedText(ctx, value, spacing);
        var cursorX = align === 'center' ? (x - (totalWidth / 2)) : x;

        for (var i = 0; i < value.length; i++) {
            var character = value.charAt(i);
            ctx.fillText(character, cursorX, y);
            cursorX += ctx.measureText(character).width;
            if (i < value.length - 1) cursorX += spacing;
        }

        return totalWidth;
    }

    function drawReceiptCenteredLines(ctx, lines, centerX, y, lineHeight) {
        lines.forEach(function (line, index) {
            var width = ctx.measureText(line).width;
            ctx.fillText(line, centerX - (width / 2), y + (index * lineHeight));
        });
    }

    function drawReceiptQrGlyph(ctx, x, y, size, color) {
        var cell = Math.max(2, Math.round(size / 7));
        var finderSize = cell * 3;
        var dotSize = Math.max(2, Math.round(cell * 1.4));

        ctx.fillStyle = color;
        ctx.strokeStyle = color;
        ctx.lineWidth = Math.max(1, Math.round(cell * 0.7));

        ctx.strokeRect(x, y, finderSize, finderSize);
        ctx.fillRect(x + cell, y + cell, cell, cell);

        ctx.strokeRect(x + (cell * 4), y, finderSize, finderSize);
        ctx.fillRect(x + (cell * 5), y + cell, cell, cell);

        ctx.strokeRect(x, y + (cell * 4), finderSize, finderSize);
        ctx.fillRect(x + cell, y + (cell * 5), cell, cell);

        ctx.fillRect(x + (cell * 4.7), y + (cell * 4.7), dotSize, dotSize);
        ctx.fillRect(x + (cell * 4.7), y + (cell * 5.9), dotSize, dotSize);
        ctx.fillRect(x + (cell * 5.9), y + (cell * 4.7), dotSize, dotSize);
    }

    function drawReceiptCaption(ctx, text, centerX, y, color) {
        var iconSize = 14;
        var gap = 10;
        var textWidth = ctx.measureText(text).width;
        var totalWidth = iconSize + gap + textWidth;
        var startX = centerX - (totalWidth / 2);

        drawReceiptQrGlyph(ctx, startX, y - 11, iconSize, color);
        ctx.fillStyle = color;
        ctx.fillText(text, startX + iconSize + gap, y);
    }

    function readReceiptNotes(root) {
        var fallback = [
            {
                tone: 'warning',
                title: 'Important:',
                copy: 'Please save this receipt image or take a screenshot. You will need your Tracking Number to track, follow up, or claim your document.'
            },
            {
                tone: 'info',
                title: 'Please note:',
                copy: 'Documents that are not received by the office within 7 days of submission will be automatically archived. Make sure to follow up if needed.'
            }
        ];

        if (!root || !root.querySelectorAll) return fallback;

        var notes = [];
        root.querySelectorAll('.receipt-notes .note-box').forEach(function (note) {
            var titleNode = note.querySelector('.note-title');
            var copyNode = note.querySelector('.note-copy');
            var title = normalizeReceiptText(titleNode ? titleNode.textContent : '', '');
            var copy = normalizeReceiptText(copyNode ? copyNode.textContent : '', '');

            if (!title || !copy) return;

            notes.push({
                tone: note.classList.contains('note-warning') ? 'warning' : 'info',
                title: title,
                copy: copy
            });
        });

        return notes.length ? notes : fallback;
    }

    function readReceiptRows(root, selector) {
        var table = root.querySelector(selector);
        if (!table) return [];

        var rows = [];
        table.querySelectorAll('tr').forEach(function (row) {
            var cells = row.querySelectorAll('td,th');
            if (cells.length < 2) return;

            rows.push({
                label: normalizeReceiptText(cells[0].textContent, 'Detail'),
                value: normalizeReceiptText(cells[1].textContent, '-')
            });
        });

        return rows;
    }

    function buildReceiptCanvas(data) {
        var notes = Array.isArray(data.notes) && data.notes.length
            ? data.notes
            : readReceiptNotes(null);
        var canvasWidth = 1080;
        var outerPadding = 36;
        var sectionWidth = canvasWidth - (outerPadding * 2);
        var sectionX = outerPadding;
        var centerX = Math.round(canvasWidth / 2);
        var qrSize = 340;
        var sectionGap = 28;
        var noteGap = 14;
        var detailsPaddingX = 28;
        var detailsLabelWidth = 210;
        var detailsValueWidth = sectionWidth - (detailsPaddingX * 2) - detailsLabelWidth - 28;
        var notePaddingX = 22;
        var notePaddingY = 20;
        var noteIconSize = 42;
        var noteTitleWidth = 170;
        var noteContentWidth = sectionWidth - (notePaddingX * 2) - noteIconSize - noteTitleWidth - 40;
        var trackingDescription = 'Present this number or let the office scan the QR below.';
        var qrCaption = 'Scan to receive';
        var scratchCanvas = document.createElement('canvas');
        var scratchCtx = scratchCanvas.getContext('2d');

        scratchCtx.font = '500 23px Poppins, "Segoe UI", sans-serif';
        var descriptionLines = wrapReceiptCanvasText(scratchCtx, trackingDescription, sectionWidth - 120);

        var preparedRows = data.rows.map(function (row) {
            scratchCtx.font = '500 22px Poppins, "Segoe UI", sans-serif';
            var lines = wrapReceiptCanvasText(scratchCtx, row.value, detailsValueWidth);
            var rowHeight = Math.max(76, 22 + (lines.length * 30) + 20);
            return {
                label: row.label,
                value: row.value,
                lines: lines,
                height: rowHeight
            };
        });

        var preparedNotes = notes.map(function (note) {
            scratchCtx.font = '500 20px Poppins, "Segoe UI", sans-serif';
            var lines = wrapReceiptCanvasText(scratchCtx, note.copy, noteContentWidth);
            var textHeight = lines.length * 28;
            var height = Math.max(92, (notePaddingY * 2) + textHeight);
            return {
                tone: note.tone,
                title: note.title,
                copy: note.copy,
                lines: lines,
                height: height
            };
        });

        var detailsTotalHeight = 0;
        preparedRows.forEach(function (row, index) {
            detailsTotalHeight += row.height;
            if (index < preparedRows.length - 1) detailsTotalHeight += 0;
        });

        var notesTotalHeight = 0;
        preparedNotes.forEach(function (note, index) {
            notesTotalHeight += note.height;
            if (index < preparedNotes.length - 1) notesTotalHeight += noteGap;
        });

        var trackingFontSize = 132;
        while (trackingFontSize > 82) {
            scratchCtx.font = '700 ' + trackingFontSize + 'px Consolas, "Courier New", monospace';
            if (scratchCtx.measureText(data.trackingNumber).width <= sectionWidth - 60) break;
            trackingFontSize -= 4;
        }

        var trackingSectionHeight = 46 + trackingFontSize + 32 + (descriptionLines.length * 30);
        var qrSectionHeight = qrSize + 50 + 26;
        var detailsCardHeight = 60 + detailsTotalHeight + 18;
        var canvasHeight =
            54 +
            trackingSectionHeight +
            sectionGap +
            qrSectionHeight +
            sectionGap +
            notesTotalHeight +
            18 +
            detailsCardHeight +
            42;

        var canvas = document.createElement('canvas');
        canvas.width = canvasWidth;
        canvas.height = canvasHeight;
        var ctx = canvas.getContext('2d');

        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        var cursorY = 54;

        ctx.fillStyle = '#334155';
        ctx.font = '700 34px Poppins, "Segoe UI", sans-serif';
        drawReceiptSpacedText(ctx, 'TRACKING NUMBER', centerX, cursorY + 34, 10, 'center');

        ctx.fillStyle = '#0056b3';
        ctx.font = '700 ' + trackingFontSize + 'px Consolas, "Courier New", monospace';
        var trackingWidth = ctx.measureText(data.trackingNumber).width;
        ctx.fillText(data.trackingNumber, centerX - (trackingWidth / 2), cursorY + 34 + 34 + trackingFontSize);

        ctx.fillStyle = '#64748b';
        ctx.font = '500 23px Poppins, "Segoe UI", sans-serif';
        drawReceiptCenteredLines(
            ctx,
            descriptionLines,
            centerX,
            cursorY + 34 + 34 + trackingFontSize + 34,
            30
        );

        cursorY += trackingSectionHeight + sectionGap;

        var qrX = centerX - Math.round(qrSize / 2);
        ctx.drawImage(data.qrImage, qrX, cursorY, qrSize, qrSize);

        ctx.fillStyle = '#64748b';
        ctx.font = '600 23px Poppins, "Segoe UI", sans-serif';
        drawReceiptCaption(ctx, qrCaption, centerX, cursorY + qrSize + 40, '#64748b');

        cursorY += qrSectionHeight + sectionGap;

        preparedNotes.forEach(function (note, index) {
            drawReceiptRoundedRect(ctx, sectionX, cursorY, sectionWidth, note.height, 18, '#ffffff', '#e5e7eb', 2);

            var circleX = sectionX + notePaddingX + Math.round(noteIconSize / 2);
            var circleY = cursorY + notePaddingY + Math.round(noteIconSize / 2);
            ctx.beginPath();
            ctx.arc(circleX, circleY, Math.round(noteIconSize / 2), 0, Math.PI * 2);
            ctx.closePath();
            ctx.fillStyle = '#f8fafc';
            ctx.fill();

            ctx.fillStyle = '#64748b';
            ctx.font = note.tone === 'warning'
                ? '700 28px Poppins, "Segoe UI", sans-serif'
                : '700 26px Poppins, "Segoe UI", sans-serif';
            var iconText = note.tone === 'warning' ? '!' : 'i';
            var iconWidth = ctx.measureText(iconText).width;
            ctx.fillText(iconText, circleX - (iconWidth / 2), circleY + 10);

            ctx.fillStyle = '#0f172a';
            ctx.font = '700 18px Poppins, "Segoe UI", sans-serif';
            ctx.fillText(note.title, sectionX + notePaddingX + noteIconSize + 18, cursorY + 42);

            ctx.fillStyle = '#334155';
            ctx.font = '500 20px Poppins, "Segoe UI", sans-serif';
            drawReceiptLines(
                ctx,
                note.lines,
                sectionX + notePaddingX + noteIconSize + noteTitleWidth + 34,
                cursorY + 42,
                28
            );

            cursorY += note.height;
            if (index < preparedNotes.length - 1) cursorY += noteGap;
        });

        cursorY += 18;

        drawReceiptRoundedRect(ctx, sectionX, cursorY, sectionWidth, detailsCardHeight, 22, '#ffffff', '#e2e8f0', 2);
        ctx.fillStyle = '#64748b';
        ctx.font = '700 18px Poppins, "Segoe UI", sans-serif';
        drawReceiptSpacedText(ctx, 'DOCUMENT DETAILS', sectionX + 26, cursorY + 32, 4, 'left');

        var rowY = cursorY + 54;
        preparedRows.forEach(function (row, index) {
            var rowTop = rowY;

            ctx.fillStyle = '#59708b';
            ctx.font = '700 18px Poppins, "Segoe UI", sans-serif';
            ctx.fillText(String(row.label || '').toUpperCase(), sectionX + detailsPaddingX, rowTop + 28);

            ctx.fillStyle = '#1b263b';
            ctx.font = '500 22px Poppins, "Segoe UI", sans-serif';
            drawReceiptLines(
                ctx,
                row.lines,
                sectionX + detailsPaddingX + detailsLabelWidth,
                rowTop + 28,
                30
            );

            rowY += row.height;

            if (index < preparedRows.length - 1) {
                ctx.beginPath();
                ctx.moveTo(sectionX + detailsPaddingX, rowY);
                ctx.lineTo(sectionX + sectionWidth - detailsPaddingX, rowY);
                ctx.lineWidth = 2;
                ctx.strokeStyle = '#e2e8f0';
                ctx.stroke();
            }
        });

        return canvas;
    }

    function triggerReceiptDownload(url, filename) {
        var anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = filename;
        anchor.rel = 'noopener';
        anchor.style.display = 'none';
        document.body.appendChild(anchor);
        anchor.click();
        setTimeout(function () {
            if (anchor.parentNode) anchor.parentNode.removeChild(anchor);
        }, 0);
    }

    function downloadReceiptCanvas(canvas, filename) {
        return new Promise(function (resolve, reject) {
            if (!canvas) {
                reject(new Error('Receipt image could not be generated.'));
                return;
            }

            if (typeof canvas.toBlob === 'function') {
                canvas.toBlob(function (blob) {
                    if (!blob) {
                        reject(new Error('Receipt image could not be generated.'));
                        return;
                    }

                    var url = URL.createObjectURL(blob);
                    triggerReceiptDownload(url, filename);
                    setTimeout(function () {
                        URL.revokeObjectURL(url);
                    }, 1200);
                    resolve();
                }, 'image/png');
                return;
            }

            try {
                triggerReceiptDownload(canvas.toDataURL('image/png'), filename);
                resolve();
            } catch (error) {
                reject(error);
            }
        });
    }

    var RECEIPT_MODAL_STYLE_ID = 'doc-trax-receipt-modal-style';
    var RECEIPT_MODAL_ID = 'doc-trax-receipt-modal';

    function ensureReceiptDownloadModalStyle() {
        if (document.getElementById(RECEIPT_MODAL_STYLE_ID)) return;

        var style = document.createElement('style');
        style.id = RECEIPT_MODAL_STYLE_ID;
        style.textContent =
            '#' + RECEIPT_MODAL_ID + '{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;' +
            'padding:20px;background:rgba(15,23,42,.46);opacity:0;pointer-events:none;z-index:100002;' +
            'transition:opacity .2s ease}' +
            '#' + RECEIPT_MODAL_ID + '.show{opacity:1;pointer-events:auto}' +
            '#' + RECEIPT_MODAL_ID + ' .doc-trax-receipt-modal-card{width:min(100%,420px);background:#fff;border-radius:26px;' +
            'padding:28px 24px 24px;box-shadow:0 24px 50px rgba(15,23,42,.22);text-align:center}' +
            '#' + RECEIPT_MODAL_ID + ' .doc-trax-receipt-modal-icon{width:58px;height:58px;margin:0 auto 16px;border-radius:999px;' +
            'display:flex;align-items:center;justify-content:center;background:#eff6ff;color:#1d4ed8;font-size:24px}' +
            '#' + RECEIPT_MODAL_ID + ' .doc-trax-receipt-modal-title{font:700 22px Poppins,"Segoe UI",sans-serif;color:#0f172a;' +
            'margin:0 0 10px}' +
            '#' + RECEIPT_MODAL_ID + ' .doc-trax-receipt-modal-text{font:500 14px/1.65 Poppins,"Segoe UI",sans-serif;color:#475569;' +
            'margin:0 0 22px}' +
            '#' + RECEIPT_MODAL_ID + ' .doc-trax-receipt-modal-button{min-width:112px;padding:11px 20px;border:none;border-radius:14px;' +
            'background:#0056b3;color:#fff;font:600 14px Poppins,"Segoe UI",sans-serif;cursor:pointer}' +
            '#' + RECEIPT_MODAL_ID + ' .doc-trax-receipt-modal-button:hover{background:#004494}' +
            '@media (max-width:520px){' +
            '#' + RECEIPT_MODAL_ID + '{padding:16px}' +
            '#' + RECEIPT_MODAL_ID + ' .doc-trax-receipt-modal-card{border-radius:22px;padding:24px 18px 20px}' +
            '#' + RECEIPT_MODAL_ID + ' .doc-trax-receipt-modal-title{font-size:19px}' +
            '#' + RECEIPT_MODAL_ID + ' .doc-trax-receipt-modal-text{font-size:13px}' +
            '}';
        document.head.appendChild(style);
    }

    function hideReceiptDownloadModal() {
        var modal = document.getElementById(RECEIPT_MODAL_ID);
        if (!modal) return;
        modal.classList.remove('show');
    }

    function ensureReceiptDownloadModal() {
        ensureReceiptDownloadModalStyle();

        var existing = document.getElementById(RECEIPT_MODAL_ID);
        if (existing) return existing;
        if (!document.body) return null;

        var modal = document.createElement('div');
        modal.id = RECEIPT_MODAL_ID;
        modal.innerHTML =
            '<div class="doc-trax-receipt-modal-card" role="dialog" aria-modal="true" aria-labelledby="docTraxReceiptModalTitle">' +
                '<div class="doc-trax-receipt-modal-icon" aria-hidden="true"><i class="fas fa-circle-check"></i></div>' +
                '<h3 class="doc-trax-receipt-modal-title" id="docTraxReceiptModalTitle">Thank you for downloading your receipt.</h3>' +
                '<p class="doc-trax-receipt-modal-text">Please keep this image for tracking, follow-up, claiming, or printing your document.</p>' +
                '<button type="button" class="doc-trax-receipt-modal-button">Done</button>' +
            '</div>';

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                hideReceiptDownloadModal();
            }
        });

        modal.querySelector('.doc-trax-receipt-modal-button').addEventListener('click', hideReceiptDownloadModal);
        document.body.appendChild(modal);
        return modal;
    }

    function showReceiptDownloadModal() {
        var modal = ensureReceiptDownloadModal();
        if (!modal) return;

        modal.classList.add('show');
        var doneButton = modal.querySelector('.doc-trax-receipt-modal-button');
        if (doneButton) doneButton.focus();
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            hideReceiptDownloadModal();
        }
    });

    function setReceiptSaveButtonState(button, busy) {
        if (!button) return;

        if (busy) {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            return;
        }

        button.disabled = false;
        button.removeAttribute('aria-busy');
    }

    async function saveReceiptImageInternal(options) {
        options = options || {};

        var button = options.button || null;
        var root = options.root || (button && button.closest ? button.closest('[data-receipt-root]') : null) || document;
        var trackingSelector = options.trackingSelector || '#generatedCode';
        var qrSelector = options.qrSelector || '#qrImg';
        var detailsSelector = options.detailsSelector || '#detailSummary';
        var trackingNode = root.querySelector(trackingSelector);
        var qrNode = root.querySelector(qrSelector);
        var trackingNumber = normalizeReceiptText(trackingNode ? trackingNode.textContent : '', '');
        var rows = readReceiptRows(root, detailsSelector);

        if (!trackingNumber) {
            throw new Error('Tracking number is not ready yet.');
        }

        if (!rows.length) {
            throw new Error('Receipt details are not ready yet.');
        }

        if (!qrNode || !normalizeReceiptText(qrNode.getAttribute('src') || qrNode.src, '')) {
            throw new Error('QR code is not ready yet.');
        }

        setReceiptSaveButtonState(button, true);

        try {
            if (document.fonts && document.fonts.ready) {
                try {
                    await document.fonts.ready;
                } catch (_) {}
            }

            await waitForImageElement(qrNode);
            var qrImage = await loadReceiptImage(qrNode.currentSrc || qrNode.src);
            var canvas = buildReceiptCanvas({
                trackingNumber: trackingNumber,
                qrImage: qrImage,
                rows: rows,
                notes: readReceiptNotes(root)
            });
            var filename = 'DOCTRAX-Receipt-' + sanitizeReceiptFilePart(trackingNumber) + '.png';
            await downloadReceiptCanvas(canvas, filename);
            showReceiptDownloadModal();
            return true;
        } finally {
            setReceiptSaveButtonState(button, false);
        }
    }

    window.saveReceiptImage = function (eventOrOptions) {
        var options = eventOrOptions || {};

        if (eventOrOptions && typeof eventOrOptions.preventDefault === 'function') {
            eventOrOptions.preventDefault();
            options = {
                button: eventOrOptions.currentTarget || eventOrOptions.target
            };
        }

        return saveReceiptImageInternal(options).catch(function (error) {
            console.error(error);
            window.alert(error && error.message ? error.message : 'Could not save the receipt image yet. Please try again.');
            return false;
        });
    };

    document.addEventListener('click', function (event) {
        var button = event.target && event.target.closest ? event.target.closest('[data-save-receipt-image]') : null;
        if (!button) return;

        event.preventDefault();
        window.saveReceiptImage({ button: button });
    });

    // Network notices and fetch helpers
    var _fetchTimestamps = {};
    var FETCH_COOLDOWN = 1000;
    var DEFAULT_REQUEST_TIMEOUT = 15000;
    var NETWORK_NOTICE_STYLE_ID = 'doc-trax-network-notice-style';
    var NETWORK_NOTICE_ID = 'doc-trax-network-notice';
    var _noticeTimer = null;
    var _statusNotices = {};

    function ensureNetworkNoticeStyle() {
        if (document.getElementById(NETWORK_NOTICE_STYLE_ID)) return;

        var style = document.createElement('style');
        style.id = NETWORK_NOTICE_STYLE_ID;
        style.textContent =
            '#' + NETWORK_NOTICE_ID + '{position:fixed;top:14px;left:50%;transform:translateX(-50%) translateY(-8px);' +
            'width:min(calc(100vw - 24px),560px);display:flex;align-items:flex-start;gap:10px;' +
            'padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0;background:#fff;color:#0f172a;' +
            'box-shadow:0 18px 40px rgba(15,23,42,.16);opacity:0;pointer-events:none;z-index:100001;' +
            'transition:opacity .2s ease,transform .2s ease}' +
            '#' + NETWORK_NOTICE_ID + '.show{opacity:1;pointer-events:auto;transform:translateX(-50%) translateY(0)}' +
            '#' + NETWORK_NOTICE_ID + '.warning{border-color:#fcd34d;background:#fffbeb;color:#92400e}' +
            '#' + NETWORK_NOTICE_ID + '.error{border-color:#fca5a5;background:#fef2f2;color:#991b1b}' +
            '#' + NETWORK_NOTICE_ID + '.success{border-color:#86efac;background:#f0fdf4;color:#166534}' +
            '#' + NETWORK_NOTICE_ID + ' .doc-trax-network-icon{width:18px;flex:0 0 18px;text-align:center;font-size:14px;line-height:1.4}' +
            '#' + NETWORK_NOTICE_ID + ' .doc-trax-network-text{font-size:12.5px;font-weight:600;line-height:1.45}';
        document.head.appendChild(style);
    }

    function ensureNetworkNotice() {
        ensureNetworkNoticeStyle();

        var existing = document.getElementById(NETWORK_NOTICE_ID);
        if (existing) return existing;
        if (!document.body) return null;

        var notice = document.createElement('div');
        notice.id = NETWORK_NOTICE_ID;
        notice.innerHTML =
            '<div class="doc-trax-network-icon" aria-hidden="true"></div>' +
            '<div class="doc-trax-network-text"></div>';
        document.body.appendChild(notice);
        return notice;
    }

    function pickNoticeIcon(type) {
        if (type === 'success') return 'fa-check-circle';
        if (type === 'warning') return 'fa-wifi';
        return 'fa-circle-exclamation';
    }

    function renderNetworkNotice(payload) {
        var notice = ensureNetworkNotice();
        if (!notice) {
            document.addEventListener('DOMContentLoaded', function handleReady() {
                document.removeEventListener('DOMContentLoaded', handleReady);
                renderNetworkNotice(payload);
            });
            return;
        }

        var type = payload && payload.type ? payload.type : 'warning';
        var iconClass = payload && payload.icon ? payload.icon : pickNoticeIcon(type);

        notice.className = type + ' show';
        notice.querySelector('.doc-trax-network-icon').innerHTML = '<i class="fas ' + iconClass + '"></i>';
        notice.querySelector('.doc-trax-network-text').textContent =
            payload && payload.message ? payload.message : 'Connection problem detected.';
    }

    function removeNetworkNotice() {
        clearTimeout(_noticeTimer);
        _noticeTimer = null;
        _statusNotices = {};

        var notice = document.getElementById(NETWORK_NOTICE_ID);
        if (notice && notice.parentNode) {
            notice.parentNode.removeChild(notice);
        }
    }

    window.setStatusNotice = function (id, message, options) {
        removeNetworkNotice();
    };

    window.clearStatusNotice = function (id) {
        removeNetworkNotice();
    };

    window.showNetworkNotice = function (message, options) {
        removeNetworkNotice();
    };

    window.hideNetworkNotice = function () {
        removeNetworkNotice();
    };

    function createRequestError(type, message, cause) {
        var error = cause instanceof Error ? cause : new Error(message);
        error.requestType = type;
        error.userMessage = message;
        if (cause) error.originalError = cause;
        return error;
    }

    function describeRequestError(error, fallbackMessage) {
        if (error && error.userMessage) return error.userMessage;
        if (error && error.requestType === 'timeout') return 'The server is taking too long to respond. Please try again.';
        if (error && error.requestType === 'offline') return 'You appear to be offline. Please reconnect and try again.';
        if (error && error.requestType === 'server') return 'The server returned an unexpected response. Please try again.';
        if (navigator.onLine === false) return 'You appear to be offline. Please reconnect and try again.';
        return fallbackMessage || 'Could not connect to the server. Please try again.';
    }

    window.createRequestError = createRequestError;
    window.describeRequestError = describeRequestError;
    window.isRequestErrorType = function (error, type) {
        return !!(error && error.requestType === type);
    };

    window.docTraxFetch = function (url, opts) {
        opts = opts || {};

        if (navigator.onLine === false && opts.skipOfflineCheck !== true) {
            return Promise.reject(createRequestError('offline', 'You appear to be offline. Please reconnect and try again.'));
        }

        var fetchOptions = {};
        Object.keys(opts).forEach(function (key) {
            if (
                key === 'timeoutMs' ||
                key === 'skipOfflineCheck' ||
                key === 'showNoticeOnError' ||
                key === 'noticeMessage' ||
                key === 'cooldownMs' ||
                key === 'rateLimitKey' ||
                key === 'rejectOnHttpError'
            ) {
                return;
            }
            fetchOptions[key] = opts[key];
        });

        var timeoutMs = typeof opts.timeoutMs === 'number' ? opts.timeoutMs : DEFAULT_REQUEST_TIMEOUT;
        var controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
        var userSignal = fetchOptions.signal;
        var timedOut = false;
        var abortRelay = null;
        var timeoutId = null;

        if (controller) {
            if (userSignal) {
                if (userSignal.aborted) {
                    controller.abort();
                } else {
                    abortRelay = function () { controller.abort(); };
                    userSignal.addEventListener('abort', abortRelay);
                }
            }
            fetchOptions.signal = controller.signal;
        }

        if (controller && timeoutMs > 0) {
            timeoutId = setTimeout(function () {
                timedOut = true;
                controller.abort();
            }, timeoutMs);
        }

        function cleanup() {
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            if (userSignal && abortRelay) {
                userSignal.removeEventListener('abort', abortRelay);
            }
        }

        return fetch(url, fetchOptions)
            .then(function (response) {
                cleanup();
                return response;
            })
            .catch(function (error) {
                cleanup();

                if (error && error.name === 'AbortError' && userSignal && userSignal.aborted && !timedOut) {
                    throw error;
                }

                var requestError;
                if (timedOut) {
                    requestError = createRequestError('timeout', 'The server is taking too long to respond. Please try again.', error);
                } else if (navigator.onLine === false) {
                    requestError = createRequestError('offline', 'You appear to be offline. Please reconnect and try again.', error);
                } else {
                    requestError = createRequestError('network', 'Could not connect to the server. Please try again.', error);
                }

                if (opts.showNoticeOnError) {
                    window.showNetworkNotice(opts.noticeMessage || requestError.userMessage, {
                        type: requestError.requestType === 'offline' ? 'warning' : 'error'
                    });
                }

                throw requestError;
            });
    };

    window.docTraxFetchJson = function (url, opts) {
        opts = opts || {};

        var fetcher = (typeof opts.cooldownMs === 'number' || typeof opts.rateLimitKey === 'string')
            ? window.safeFetch
            : window.docTraxFetch;

        return fetcher(url, opts)
            .then(function (response) {
                if (opts.rejectOnHttpError !== false && !response.ok) {
                    if (response.status === 429) {
                        throw createRequestError('rate_limit', 'Too many requests. Please wait a moment and try again.');
                    }
                    throw createRequestError('server', 'The server returned an unexpected response. Please try again.');
                }

                return response.text().then(function (text) {
                    if (!text) return {};

                    try {
                        return JSON.parse(text);
                    } catch (parseError) {
                        throw createRequestError('server', 'Received an unexpected server response. Please try again.', parseError);
                    }
                });
            });
    };

    window.safeFetch = function (url, opts) {
        opts = opts || {};
        var key = opts.rateLimitKey || ((opts.method || 'GET') + ':' + url);
        var cooldownMs = typeof opts.cooldownMs === 'number' ? opts.cooldownMs : FETCH_COOLDOWN;
        var now = Date.now();

        if (_fetchTimestamps[key] && (now - _fetchTimestamps[key]) < cooldownMs) {
            return Promise.reject(createRequestError('rate_limit', 'Please wait a moment before trying again.'));
        }

        _fetchTimestamps[key] = now;
        return window.docTraxFetch(url, opts);
    };

    window.addEventListener('offline', removeNetworkNotice);
    window.addEventListener('online', removeNetworkNotice);
    removeNetworkNotice();

    // Back-link enhancement
    var BACK_LINK_STYLE_ID = 'doc-trax-back-link-style';
    var BACK_LINK_ICON_SVG =
        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" ' +
        'stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ' +
        'class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left">' +
        '<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>' +
        '<path d="M5 12l14 0"></path>' +
        '<path d="M5 12l6 6"></path>' +
        '<path d="M5 12l6 -6"></path>' +
        '</svg>';

    function ensureBackLinkStyle() {
        if (document.getElementById(BACK_LINK_STYLE_ID)) return;

        var style = document.createElement('style');
        style.id = BACK_LINK_STYLE_ID;
        style.textContent =
            '.doc-trax-back-link{display:inline-flex!important;align-items:center!important;gap:11px!important;' +
            'padding:0!important;border:none!important;background:transparent!important;border-radius:0!important;' +
            'box-shadow:none!important;text-decoration:none!important;color:#0f172a!important;font-weight:600!important;' +
            'line-height:1.2;transition:color .18s ease!important}' +
            '.doc-trax-back-link:hover{color:#0f172a!important}' +
            '.doc-trax-back-link:focus-visible{outline:2px solid rgba(0,86,179,.28);outline-offset:4px;border-radius:999px}' +
            '.doc-trax-back-link-icon{width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;' +
            'flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);' +
            'color:#fff;box-shadow:none!important;transition:filter .18s ease,background .18s ease}' +
            '.doc-trax-back-link-icon svg{display:block;width:18px;height:18px;stroke:currentColor}' +
            '.doc-trax-back-link:hover .doc-trax-back-link-icon{filter:brightness(.88)}' +
            '.doc-trax-back-link-text{display:inline-block}' +
            '.doc-trax-back-link-icon-only{gap:0!important}' +
            '.doc-trax-back-link-icon-only .doc-trax-back-link-text{display:none!important}' +
            '@media (max-width:640px){' +
            '.doc-trax-back-link{gap:10px!important;font-size:12px!important}' +
            '.doc-trax-back-link-icon{width:34px;height:34px;flex-basis:34px}' +
            '.doc-trax-back-link-icon svg{width:16px;height:16px}' +
            '}';
        document.head.appendChild(style);
    }

    function isBackLinkTarget(node) {
        return !!(
            node &&
            node.nodeType === 1 &&
            node.classList &&
            (
                node.classList.contains('back-link') ||
                node.classList.contains('btn-back')
            )
        );
    }

    function upgradeBackLink(node) {
        if (!isBackLinkTarget(node) || node.dataset.backLinkUpgraded === '1') return;

        ensureBackLinkStyle();

        var label = String(node.textContent || '').replace(/\s+/g, ' ').trim();
        if (!label) return;
        var isDashboardBack = /dashboard/i.test(label);

        node.dataset.backLinkUpgraded = '1';
        node.classList.add('doc-trax-back-link');
        if (isDashboardBack) {
            node.classList.add('doc-trax-back-link-icon-only');
            node.setAttribute('aria-label', label);
            node.setAttribute('title', label);
        }
        node.textContent = '';

        var icon = document.createElement('span');
        icon.className = 'doc-trax-back-link-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = BACK_LINK_ICON_SVG;

        var text = document.createElement('span');
        text.className = 'doc-trax-back-link-text';
        text.textContent = label;

        node.appendChild(icon);
        if (!isDashboardBack) {
            node.appendChild(text);
        }
    }

    function initBackLinks(root) {
        if (!root) return;

        if (isBackLinkTarget(root)) {
            upgradeBackLink(root);
        }

        if (!root.querySelectorAll) return;

        root.querySelectorAll('.back-link, .btn-back').forEach(function (link) {
            upgradeBackLink(link);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function initBackLinksOnReady() {
            initBackLinks(document);
        });
    } else {
        initBackLinks(document);
    }

    var backLinkObserver = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (!node || node.nodeType !== 1) return;
                initBackLinks(node);
            });
        });
    });
    backLinkObserver.observe(document.documentElement, { childList: true, subtree: true });

    // Idle auto-logout for authenticated screens
    var IDLE_LOGOUT_TIMEOUT = 30 * 60 * 1000;

    function isAuthenticatedPage() {
        if (document.body && document.body.getAttribute('data-authenticated') === 'true') return true;
        if (document.getElementById('mainSidebar')) return true;
        if (document.querySelector('.btn-logout')) return true;
        if (typeof window.logout === 'function' || typeof window.performLogout === 'function') return true;

        if (
            /^\/receive\/[A-Za-z0-9-]+$/i.test(window.location.pathname) &&
            document.getElementById('confirmBtn')
        ) {
            return true;
        }

        return false;
    }

    function fallbackLogout() {
        var csrfNode = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfNode ? csrfNode.getAttribute('content') : '';

        fetch('/api/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        }).catch(function () {
            // Best effort only; we still force the redirect.
        }).finally(function () {
            window.location.replace('/login');
        });
    }

    function initIdleLogoutGuard() {
        if (window.__docTraxIdleGuardInitialized) return;
        if (!isAuthenticatedPage()) return;

        window.__docTraxIdleGuardInitialized = true;
        window.__docTraxIdleLogoutMs = IDLE_LOGOUT_TIMEOUT;

        var idleTimer = null;

        function logoutForIdle() {
            if (typeof window.performLogout === 'function') {
                window.performLogout();
                return;
            }

            if (typeof window.logout === 'function') {
                window.logout();
                return;
            }

            fallbackLogout();
        }

        function resetIdleTimer() {
            if (idleTimer) clearTimeout(idleTimer);
            idleTimer = setTimeout(logoutForIdle, IDLE_LOGOUT_TIMEOUT);
        }

        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click', 'input'].forEach(function (eventName) {
            document.addEventListener(eventName, resetIdleTimer, { passive: true });
        });

        window.addEventListener('focus', resetIdleTimer);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) resetIdleTimer();
        });

        resetIdleTimer();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initIdleLogoutGuard);
    } else {
        initIdleLogoutGuard();
    }

    // Skeleton loading helpers
    var SKEL_STYLE_ID = 'skeleton-loading-style';
    if (!document.getElementById(SKEL_STYLE_ID)) {
        var style = document.createElement('style');
        style.id = SKEL_STYLE_ID;
        style.textContent =
            '.skeleton{position:relative;overflow:hidden;background:#e2e8f0;border-radius:6px;color:transparent!important}' +
            '.skeleton *{visibility:hidden}' +
            '.skeleton::after{content:"";position:absolute;inset:0;' +
            'background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,.45) 50%,transparent 100%);' +
            'animation:skeletonShimmer 1.5s ease-in-out infinite}' +
            '@keyframes skeletonShimmer{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}' +
            '.skeleton-text{height:14px;margin:6px 0;border-radius:4px}' +
            '.skeleton-text.w-50{width:50%}.skeleton-text.w-75{width:75%}.skeleton-text.w-100{width:100%}' +
            '.skeleton-heading{height:22px;width:40%;margin:8px 0;border-radius:4px}' +
            '.skeleton-stat{height:42px;border-radius:8px;margin-bottom:8px}' +
            '.skeleton-card{height:80px;border-radius:10px;margin-bottom:12px}' +
            '.skeleton-row{height:44px;border-radius:4px;margin-bottom:8px}' +
            '.skeleton-fade-in{animation:skeletonFadeIn .3s ease forwards}' +
            '@keyframes skeletonFadeIn{from{opacity:0}to{opacity:1}}';
        document.head.appendChild(style);
    }

    window.showSkeleton = function (el) {
        if (!el) return;
        el.classList.add('skeleton');
        el.classList.remove('skeleton-fade-in');
    };

    window.hideSkeleton = function (el) {
        if (!el) return;
        el.classList.remove('skeleton');
        el.classList.add('skeleton-fade-in');
    };

    // Auto-debounce client-side filterTable
    var SEARCH_DEBOUNCE_MS = 300;

    function wrapFilterTable(input) {
        if (!input || input.dataset.filterDebounced === '1') return;

        var origHandler = input.getAttribute('oninput') || input.getAttribute('onkeyup');
        if (!origHandler || origHandler.indexOf('filterTable') === -1) return;

        input.removeAttribute('oninput');
        input.removeAttribute('onkeyup');
        input.dataset.filterDebounced = '1';

        var debouncedFilter = window.debounce(function () {
            if (typeof window.filterTable === 'function') {
                window.filterTable();
            }
        }, SEARCH_DEBOUNCE_MS);

        input.addEventListener('input', debouncedFilter);
    }

    function initFilterDebounce() {
        document.querySelectorAll('input[oninput*="filterTable"], input[onkeyup*="filterTable"]')
            .forEach(wrapFilterTable);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFilterDebounce);
    } else {
        initFilterDebounce();
    }

    var debounceObserver = new MutationObserver(function (mutations) {
        var found = false;

        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType !== 1) return;

                if (
                    node.matches &&
                    (
                        node.matches('input[oninput*="filterTable"]') ||
                        node.matches('input[onkeyup*="filterTable"]')
                    )
                ) {
                    found = true;
                }

                if (node.querySelectorAll) {
                    var inputs = node.querySelectorAll('input[oninput*="filterTable"], input[onkeyup*="filterTable"]');
                    if (inputs.length) found = true;
                }
            });
        });

        if (found) initFilterDebounce();
    });
    debounceObserver.observe(document.documentElement, { childList: true, subtree: true });

    // Live SPA search/filter forms
    var LIVE_FORM_SELECTOR = 'form[data-live-search][method="GET"]';
    var LIVE_FORM_DEFAULT_DEBOUNCE_MS = 700;
    var LIVE_FORM_MIN_INTERVAL_MS = 1200;

    function buildLiveFormUrl(form) {
        var action = form.getAttribute('action') || location.pathname;
        var url = new URL(action, location.origin);
        var formData = new FormData(form);
        var params = new URLSearchParams();

        formData.forEach(function (value, key) {
            var normalized = typeof value === 'string' ? window.sanitizeInput(value) : value;
            if (normalized === null || normalized === undefined) return;
            if (String(normalized).trim() === '') return;
            params.append(key, String(normalized).trim());
        });

        url.search = params.toString();
        return url.toString();
    }

    function submitLiveForm(form) {
        var url = buildLiveFormUrl(form);
        var currentUrl = location.href.split('#')[0];

        if (form.dataset.liveSearchLastUrl === url && url === currentUrl) {
            return Promise.resolve(false);
        }

        if (form._liveSearchController && typeof form._liveSearchController.abort === 'function') {
            form._liveSearchController.abort();
        }

        form._liveSearchController = typeof AbortController !== 'undefined' ? new AbortController() : null;
        form.dataset.liveSearchLastUrl = url;

        if (typeof window.spaNavigate === 'function') {
            return window.spaNavigate(url, {
                historyMode: 'replace',
                fallbackUrl: url,
                silent: true,
                preserveScroll: true,
                preserveFocus: true,
                signal: form._liveSearchController ? form._liveSearchController.signal : undefined
            });
        }

        window.location.href = url;
        return Promise.resolve(false);
    }

    function queueLiveFormSubmit(form, isManual) {
        if (!form) return Promise.resolve(false);

        if (form._liveSearchCooldownTimer) {
            clearTimeout(form._liveSearchCooldownTimer);
            form._liveSearchCooldownTimer = null;
        }

        if (isManual) {
            form._liveSearchLastSubmittedAt = Date.now();
            return submitLiveForm(form);
        }

        var minInterval = parseInt(form.getAttribute('data-live-min-interval') || String(LIVE_FORM_MIN_INTERVAL_MS), 10);
        if (isNaN(minInterval) || minInterval < 0) minInterval = LIVE_FORM_MIN_INTERVAL_MS;

        var now = Date.now();
        var elapsed = now - (form._liveSearchLastSubmittedAt || 0);

        if (elapsed >= minInterval) {
            form._liveSearchLastSubmittedAt = now;
            return submitLiveForm(form);
        }

        form._liveSearchCooldownTimer = setTimeout(function () {
            form._liveSearchLastSubmittedAt = Date.now();
            submitLiveForm(form);
        }, minInterval - elapsed);

        return Promise.resolve(false);
    }

    function bindLiveForm(form) {
        if (!form || form.dataset.liveSearchBound === '1') return;

        form.dataset.liveSearchBound = '1';
        form.dataset.liveSearchLastUrl = location.href.split('#')[0];
        form._liveSearchLastSubmittedAt = 0;

        var delay = parseInt(form.getAttribute('data-live-debounce') || String(LIVE_FORM_DEFAULT_DEBOUNCE_MS), 10);
        if (isNaN(delay) || delay < 0) delay = LIVE_FORM_DEFAULT_DEBOUNCE_MS;

        function scheduleDebouncedSubmit() {
            if (form._liveSearchInputTimer) {
                clearTimeout(form._liveSearchInputTimer);
            }

            form._liveSearchInputTimer = setTimeout(function () {
                queueLiveFormSubmit(form, false);
            }, delay);
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (form._liveSearchInputTimer) {
                clearTimeout(form._liveSearchInputTimer);
                form._liveSearchInputTimer = null;
            }
            queueLiveFormSubmit(form, true);
        });

        form.querySelectorAll('input, textarea, select').forEach(function (field) {
            if (!field || field.hasAttribute('data-no-live-search')) return;

            var tag = field.tagName;
            var type = (field.getAttribute('type') || '').toLowerCase();

            if (tag === 'SELECT' || type === 'date' || type === 'checkbox' || type === 'radio') {
                field.addEventListener('change', function () {
                    if (form._liveSearchInputTimer) {
                        clearTimeout(form._liveSearchInputTimer);
                        form._liveSearchInputTimer = null;
                    }
                    queueLiveFormSubmit(form, false);
                });
                return;
            }

            if (type === 'hidden') return;

            if (tag === 'TEXTAREA' || type === 'text' || type === 'search' || type === '') {
                field.addEventListener('input', scheduleDebouncedSubmit);
                field.addEventListener('change', function () {
                    if (form._liveSearchInputTimer) {
                        clearTimeout(form._liveSearchInputTimer);
                        form._liveSearchInputTimer = null;
                    }
                    queueLiveFormSubmit(form, false);
                });
            }
        });
    }

    function initLiveForms() {
        document.querySelectorAll(LIVE_FORM_SELECTOR).forEach(bindLiveForm);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLiveForms);
    } else {
        initLiveForms();
    }

    var liveFormObserver = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType !== 1) return;
                if (node.matches && node.matches(LIVE_FORM_SELECTOR)) bindLiveForm(node);
                if (node.querySelectorAll) {
                    node.querySelectorAll(LIVE_FORM_SELECTOR).forEach(bindLiveForm);
                }
            });
        });
    });
    liveFormObserver.observe(document.documentElement, { childList: true, subtree: true });

    // Form submit cooldown for GET search forms
    var FORM_COOLDOWN_MS = 2000;
    var _formTimestamps = {};

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || form.tagName !== 'FORM') return;

        var method = (form.getAttribute('method') || 'POST').toUpperCase();
        if (method !== 'GET') return;
        if (form.hasAttribute('data-live-search')) return;
        if (form.hasAttribute('data-no-cooldown')) return;

        var key = form.id || form.getAttribute('action') || 'default';
        var now = Date.now();

        if (_formTimestamps[key] && (now - _formTimestamps[key]) < FORM_COOLDOWN_MS) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        _formTimestamps[key] = now;
    }, true);

    // Global logout reliability
    var _logoutInFlight = false;
    var LOGOUT_REDIRECT_DELAY_MS = 120;

    function broadcastLogoutEvent() {
        try {
            localStorage.setItem('logout-event', String(Date.now()));
        } catch (e) {}
    }

    function clearLogoutState() {
        try { sessionStorage.removeItem('logout-event'); } catch (e) {}
        broadcastLogoutEvent();
    }

    function navigateToLogin() {
        window.location.replace('/login');
    }

    function performReliableLogout(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (_logoutInFlight) return false;
        _logoutInFlight = true;

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        var redirected = false;

        clearLogoutState();

        var logoutRequest = fetch('/api/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin',
            keepalive: true
        }).catch(function () {
            // Best effort only. We'll still redirect to the login page.
        });

        setTimeout(function () {
            if (redirected) return;
            redirected = true;
            navigateToLogin();
        }, LOGOUT_REDIRECT_DELAY_MS);

        logoutRequest.finally(function () {
            if (redirected) return;
            redirected = true;
            navigateToLogin();
        });

        return false;
    }

    window.performLogout = performReliableLogout;
    window.logout = performReliableLogout;

    document.addEventListener('click', function (event) {
        var btn = event.target && event.target.closest ? event.target.closest('.btn-logout') : null;
        if (!btn) return;
        performReliableLogout(event);
    }, true);
})();
