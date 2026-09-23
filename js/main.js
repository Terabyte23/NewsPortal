// NEWSPORTAL JAVASCRIPT LAYER
document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initLiveClock();
    initBookmarks();
    initSearchModal();
    initUserMenu();
    initAudioReader();
    initReactions();
    initComments();
    initSharing();
    initNewsletter();
    initDeleteConfirmations();
});

// 1. THEME SWITCHER
function initTheme() {
    const themeBtn = document.getElementById('themeToggleBtn');
    if (!themeBtn) return;
    
    themeBtn.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('np_theme', newTheme);
        showToast(newTheme === 'dark' ? 'Tume režiim aktiivne 🌙' : 'Hele režiim aktiivne ☀️');
    });
}

// 2. LIVE CLOCK (Estonian formatting)
function initLiveClock() {
    const clockEl = document.getElementById('liveClock');
    if (!clockEl) return;
    
    const days = ['Pühapäev', 'Esmaspäev', 'Teisipäev', 'Kolmapäev', 'Neljapäev', 'Reede', 'Laupäev'];
    const months = ['jaanuar', 'veebruar', 'märts', 'aprill', 'mai', 'juuni', 'juuli', 'august', 'september', 'oktoober', 'november', 'detsember'];
    
    function updateClock() {
        const now = new Date();
        const dayName = days[now.getDay()];
        const day = now.getDate();
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        const secs = String(now.getSeconds()).padStart(2, '0');
        
        clockEl.textContent = dayName + ', ' + day + '. ' + monthName + ' ' + year + ' • ' + hours + ':' + mins + ':' + secs;
    }
    
    updateClock();
    setInterval(updateClock, 1000);
}

// 3. BOOKMARKS SYSTEM (Local Storage)
function getBookmarks() {
    try {
        return JSON.parse(localStorage.getItem('np_bookmarks')) || [];
    } catch(e) {
        return [];
    }
}

function saveBookmarks(list) {
    localStorage.setItem('np_bookmarks', JSON.stringify(list));
    updateBookmarkBadge();
}

function updateBookmarkBadge() {
    const countEl = document.getElementById('bookmarkCount');
    if (countEl) {
        const list = getBookmarks();
        countEl.textContent = list.length;
    }
}

function initBookmarks() {
    updateBookmarkBadge();
    
    const list = getBookmarks();
    document.querySelectorAll('.card-bookmark-btn, .btn-bookmark-action').forEach(btn => {
        const id = btn.dataset.id;
        if (list.some(item => item.id == id)) {
            btn.classList.add('saved');
        }
        
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleBookmark(btn);
        });
    });
}

function toggleBookmark(btn) {
    const id = btn.dataset.id;
    const title = btn.dataset.title || document.querySelector('h1, .card-title')?.textContent?.trim() || 'Uudis';
    const image = btn.dataset.image || '';
    const category = btn.dataset.category || 'Uudis';
    const date = btn.dataset.date || 'Täna';
    
    let list = getBookmarks();
    const existingIndex = list.findIndex(item => item.id == id);
    
    if (existingIndex >= 0) {
        list.splice(existingIndex, 1);
        btn.classList.remove('saved');
        showToast('Uudis eemaldatud järjehoidjatest');
    } else {
        list.push({ id: id, title: title, image: image, category: category, date: date });
        btn.classList.add('saved');
        showToast('Uudis salvestatud järjehoidjatesse! 📑');
    }
    
    saveBookmarks(list);
}

// 4. LIVE SEARCH MODAL
function initSearchModal() {
    const modal = document.getElementById('searchModal');
    const openBtn = document.getElementById('openSearchBtn');
    const closeBtn = document.getElementById('closeSearchBtn');
    const backdrop = document.getElementById('searchModalBackdrop');
    const input = document.getElementById('modalSearchInput');
    const resultsBox = document.getElementById('modalSearchResults');
    
    if (!modal || !openBtn) return;
    
    function openModal() {
        modal.classList.add('active');
        input.focus();
    }
    
    function closeModal() {
        modal.classList.remove('active');
        input.value = '';
        resultsBox.innerHTML = '<div class="search-hint">Sisesta vähemalt 2 tähemärki reaalajas otsinguks</div>';
    }
    
    openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);
    
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            modal.classList.contains('active') ? closeModal() : openModal();
        }
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });
    
    let debounceTimer;
    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = input.value.trim();
        
        if (query.length < 2) {
            resultsBox.innerHTML = '<div class="search-hint">Sisesta vähemalt 2 tähemärki reaalajas otsinguks</div>';
            return;
        }
        
        resultsBox.innerHTML = '<div class="search-hint">Otsin tulemusi...</div>';
        
        debounceTimer = setTimeout(() => {
            const apiPath = window.location.pathname.includes('/admin/') ? '../api/search.php' : 'api/search.php';
            fetch(apiPath + '?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    if (!data.results || data.results.length === 0) {
                        resultsBox.innerHTML = '<div class="search-hint">Vasteid ei leitud. Proovi teist märksõna.</div>';
                        return;
                    }
                    
                    let html = '';
                    data.results.forEach(item => {
                        const newsUrl = window.location.pathname.includes('/admin/') ? ('../news.php?id=' + item.id) : ('news.php?id=' + item.id);
                        html += '<a href="' + newsUrl + '" class="search-result-item">' +
                                '<div style="width: 60px; height: 45px; border-radius: 6px; overflow: hidden; flex-shrink: 0; background: #222;">' +
                                '<img src="' + item.image + '" style="width:100%; height:100%; object-fit: cover;" alt="">' +
                                '</div>' +
                                '<div style="flex-grow:1;">' +
                                '<div class="search-result-title">' + escapeHtml(item.title) + '</div>' +
                                '<div style="font-size: 0.75rem; color: var(--text-muted);">' + escapeHtml(item.category) + ' • ' + escapeHtml(item.date) + '</div>' +
                                '</div>' +
                                '</a>';
                    });
                    resultsBox.innerHTML = html;
                })
                .catch(err => {
                    resultsBox.innerHTML = '<div class="search-hint">Viga otsingu laadimisel.</div>';
                });
        }, 250);
    });
}

// 5. USER MENU DROPDOWN
function initUserMenu() {
    const userBtn = document.getElementById('userMenuBtn');
    const dropdown = document.getElementById('userDropdown');
    
    if (!userBtn || !dropdown) return;
    
    userBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });
    
    document.addEventListener('click', () => {
        dropdown.classList.remove('show');
    });
}

// 6. INTERACTIVE TEXT-TO-SPEECH (TTS) AUDIO READER
function initAudioReader() {
    const playBtn = document.getElementById('ttsPlayBtn');
    const statusEl = document.getElementById('ttsStatus');
    const progressFill = document.getElementById('ttsProgressFill');
    const articleBody = document.querySelector('.article-body-wrapper');
    
    if (!playBtn || !articleBody || !('speechSynthesis' in window)) {
        const widget = document.getElementById('audioWidget');
        if (widget && !('speechSynthesis' in window)) {
            widget.style.display = 'none';
        }
        return;
    }
    
    let isPlaying = false;
    let utterance = null;
    const textToRead = articleBody.innerText;
    
    playBtn.addEventListener('click', () => {
        if (isPlaying) {
            window.speechSynthesis.cancel();
            isPlaying = false;
            playBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>';
            statusEl.textContent = 'Kuulamine peatatud';
            progressFill.style.width = '0%';
            return;
        }
        
        window.speechSynthesis.cancel();
        utterance = new SpeechSynthesisUtterance(textToRead);
        utterance.lang = 'et-EE';
        utterance.rate = 1.0;
        
        const voices = window.speechSynthesis.getVoices();
        const estonianVoice = voices.find(v => v.lang.startsWith('et')) || voices.find(v => v.lang.startsWith('en')) || voices[0];
        if (estonianVoice) utterance.voice = estonianVoice;
        
        utterance.onstart = () => {
            isPlaying = true;
            playBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>';
            statusEl.textContent = 'Heliesitus käib...';
            showToast('Artikli heliesitus käivitatud 🎧');
        };
        
        utterance.onboundary = (e) => {
            if (e.charIndex && textToRead.length) {
                const percent = Math.min(100, Math.round((e.charIndex / textToRead.length) * 100));
                progressFill.style.width = percent + '%';
            }
        };
        
        utterance.onend = () => {
            isPlaying = false;
            playBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>';
            statusEl.textContent = 'Heliesitus lõpetatud';
            progressFill.style.width = '100%';
            setTimeout(() => { progressFill.style.width = '0%'; }, 2000);
        };
        
        utterance.onerror = () => {
            isPlaying = false;
            playBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>';
            statusEl.textContent = 'Heliesituse viga';
        };
        
        window.speechSynthesis.speak(utterance);
    });
    
    const zoomIn = document.getElementById('fontSizeIn');
    const zoomOut = document.getElementById('fontSizeOut');
    if (zoomIn && zoomOut) {
        let currentSize = 1.125;
        zoomIn.addEventListener('click', () => {
            currentSize = Math.min(1.5, currentSize + 0.1);
            articleBody.style.fontSize = currentSize + 'rem';
            showToast('Kirja suurus suurendatud');
        });
        zoomOut.addEventListener('click', () => {
            currentSize = Math.max(0.9, currentSize - 0.1);
            articleBody.style.fontSize = currentSize + 'rem';
            showToast('Kirja suurus vähendatud');
        });
    }
}

// 7. REACTIONS BAR
function initReactions() {
    const reactionButtons = document.querySelectorAll('.reaction-btn');
    if (!reactionButtons.length) return;
    
    reactionButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const newsId = btn.dataset.newsId;
            const type = btn.dataset.type;
            
            btn.disabled = true;
            
            fetch('api/reactions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ news_id: newsId, type: type })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    const countSpan = btn.querySelector('.reaction-count');
                    if (countSpan) countSpan.textContent = data.new_count;
                    btn.classList.toggle('active');
                    showToast('Täname reaktsiooni eest! ✨');
                }
            })
            .catch(() => {
                btn.disabled = false;
            });
        });
    });
}

// 8. COMMENTS AJAX
function initComments() {
    const form = document.getElementById('commentForm');
    const list = document.getElementById('commentsList');
    const countBadge = document.getElementById('commentsCountBadge');
    
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const newsId = form.dataset.newsId;
            const textarea = form.querySelector('textarea[name="text"]');
            const authorInput = form.querySelector('input[name="author_name"]');
            const text = textarea ? textarea.value.trim() : '';
            const author = authorInput ? authorInput.value.trim() : 'Lugeja';
            
            if (!text) return;
            
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            
            fetch('api/comments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ news_id: newsId, text: text, author_name: author })
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) submitBtn.disabled = false;
                if (data.success) {
                    textarea.value = '';
                    showToast('Kommentaar edukalt lisatud!');
                    
                    const isAdmin = list && list.dataset.isAdmin === '1';
                    const deleteBtnHtml = isAdmin ? 
                        '<a href="news.php?id=' + encodeURIComponent(newsId) + '&del_comment=' + encodeURIComponent(data.comment.id) + '" class="comment-delete-btn" data-comment-id="' + data.comment.id + '" title="Kustuta kommentaar">' +
                            '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>' +
                            ' Kustuta' +
                        '</a>' : '';

                    const card = document.createElement('div');
                    card.className = 'comment-card';
                    card.setAttribute('data-comment-id', data.comment.id);
                    card.innerHTML = 
                        '<div class="comment-top">' +
                            '<div class="comment-author-badge">' +
                                '<div class="avatar-circle" style="width:28px; height:28px; font-size:0.75rem;">' + (data.comment.author || 'L')[0].toUpperCase() + '</div>' +
                                '<span class="comment-author-name">' + escapeHtml(data.comment.author || 'Lugeja') + '</span>' +
                                '<span class="user-role-badge role-user">Lugeja</span>' +
                            '</div>' +
                            '<div style="display: flex; align-items: center; gap: 10px;">' +
                                '<span class="comment-date">Just praegu</span>' +
                                deleteBtnHtml +
                            '</div>' +
                        '</div>' +
                        '<p class="comment-text">' + escapeHtml(data.comment.text) + '</p>';
                    
                    if (list) {
                        const noComments = list.querySelector('.no-comments-msg');
                        if (noComments) noComments.remove();
                        list.prepend(card);
                    }
                    if (countBadge) {
                        const cur = parseInt(countBadge.textContent) || 0;
                        countBadge.textContent = cur + 1;
                    }
                } else {
                    showToast(data.error || 'Viga kommentaari saatmisel');
                }
            })
            .catch(() => {
                if (submitBtn) submitBtn.disabled = false;
                showToast('Võrgu viga kommentaari salvestamisel');
            });
        });
    }

    // Comment deletion listener (for admin on news.php)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.comment-delete-btn');
        if (!btn) return;
        e.preventDefault();

        const commentId = btn.dataset.commentId;
        const card = btn.closest('.comment-card');

        showConfirmModal({
            title: 'Kommentaari kustutamine',
            message: 'Kas soovid selle kommentaari kindlasti kustutada?',
            confirmText: 'Kustuta',
            onConfirm: () => {
                btn.disabled = true;
                fetch(`api/comments.php?action=delete&id=${encodeURIComponent(commentId)}`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast('Kommentaar edukalt kustutatud!');
                        if (card) {
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'translateY(-10px)';
                            setTimeout(() => {
                                card.remove();
                                if (countBadge) {
                                    const cur = Math.max(0, (parseInt(countBadge.textContent) || 1) - 1);
                                    countBadge.textContent = cur;
                                }
                                if (list && list.querySelectorAll('.comment-card').length === 0) {
                                    list.innerHTML = '<div class="no-comments-msg" style="text-align: center; padding: 30px; color: var(--text-muted);">' +
                                        'Ole esimene, kes selle uudise kohta arvamust avaldab!' +
                                    '</div>';
                                }
                            }, 300);
                        }
                    } else {
                        const href = btn.getAttribute('href');
                        if (href && href !== '#' && !href.startsWith('javascript:')) {
                            window.location.href = href;
                        } else {
                            btn.disabled = false;
                            showToast(data.error || 'Viga kommentaari kustutamisel');
                        }
                    }
                })
                .catch(() => {
                    const href = btn.getAttribute('href');
                    if (href && href !== '#' && !href.startsWith('javascript:')) {
                        window.location.href = href;
                    } else {
                        btn.disabled = false;
                        showToast('Võrgu viga kommentaari kustutamisel');
                    }
                });
            }
        });
    });
}

// 9. SHARING & COPY LINK
function initSharing() {
    const copyBtn = document.getElementById('copyLinkBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(window.location.href)
                .then(() => showToast('Artikli link kopeeritud lõikelauale! 📋'))
                .catch(() => showToast('Lingi kopeerimine ebaõnnestus'));
        });
    }
}

// 10. NEWSLETTER
function initNewsletter() {
    const forms = document.querySelectorAll('#newsletterForm, .newsletter-form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const input = form.querySelector('input[type="email"]');
            if (input && input.value) {
                showToast('Täname tellimast! Uudiskiri kinnitatud: ' + input.value);
                input.value = '';
            }
        });
    });
}

// TOAST HELPER
function showToast(message) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = '<span>⚡</span> <span>' + message + '</span>';
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = '0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3200);
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// 11. GLOBAL CONFIRMATION MODAL SYSTEM
let activeConfirmCallback = null;

function getOrInitConfirmModal() {
    let overlay = document.getElementById('npConfirmModalOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'npConfirmModalOverlay';
        overlay.className = 'np-modal-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.innerHTML = 
            '<div class="np-modal-dialog">' +
                '<div class="np-modal-header">' +
                    '<div class="np-modal-icon-badge">' +
                        '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                            '<polyline points="3 6 5 6 21 6"></polyline>' +
                            '<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>' +
                            '<line x1="10" y1="11" x2="10" y2="17"></line>' +
                            '<line x1="14" y1="11" x2="14" y2="17"></line>' +
                        '</svg>' +
                    '</div>' +
                    '<div class="np-modal-title-area">' +
                        '<h3 class="np-modal-title" id="npConfirmModalTitle">Kinnita kustutamine</h3>' +
                        '<p class="np-modal-message" id="npConfirmModalMessage">Kas oled kindel, et soovid selle elemendi kustutada? Seda tegevust ei saa tagasi võtta.</p>' +
                    '</div>' +
                '</div>' +
                '<div class="np-modal-footer">' +
                    '<button type="button" class="np-modal-btn np-modal-btn-cancel" id="npConfirmModalCancel">Tühista</button>' +
                    '<button type="button" class="np-modal-btn np-modal-btn-confirm" id="npConfirmModalSubmit">' +
                        '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                            '<polyline points="3 6 5 6 21 6"></polyline>' +
                            '<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>' +
                        '</svg>' +
                        '<span id="npConfirmModalBtnText">Jah, kustuta</span>' +
                    '</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);

        const cancelBtn = overlay.querySelector('#npConfirmModalCancel');
        const submitBtn = overlay.querySelector('#npConfirmModalSubmit');

        cancelBtn.addEventListener('click', closeConfirmModal);
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeConfirmModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && overlay.classList.contains('active')) {
                closeConfirmModal();
            }
        });

        submitBtn.addEventListener('click', () => {
            const cb = activeConfirmCallback;
            closeConfirmModal();
            if (typeof cb === 'function') {
                cb();
            }
        });
    }
    return overlay;
}

function showConfirmModal(opts) {
    const options = opts || {};
    const overlay = getOrInitConfirmModal();
    const titleEl = overlay.querySelector('#npConfirmModalTitle');
    const msgEl = overlay.querySelector('#npConfirmModalMessage');
    const btnTextEl = overlay.querySelector('#npConfirmModalBtnText');
    const cancelBtn = overlay.querySelector('#npConfirmModalCancel');

    if (titleEl) titleEl.textContent = options.title || 'Kinnita kustutamine';
    if (msgEl) msgEl.textContent = options.message || 'Kas oled kindel, et soovid selle elemendi kustutada? Seda tegevust ei saa tagasi võtta.';
    if (btnTextEl) btnTextEl.textContent = options.confirmText || 'Jah, kustuta';
    if (cancelBtn && options.cancelText) cancelBtn.textContent = options.cancelText;

    activeConfirmCallback = options.onConfirm || null;
    overlay.classList.add('active');

    setTimeout(() => {
        if (cancelBtn) cancelBtn.focus();
    }, 60);
}

function closeConfirmModal() {
    const overlay = document.getElementById('npConfirmModalOverlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
    activeConfirmCallback = null;
}

function initDeleteConfirmations() {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.action-btn-del, [data-confirm-delete]');
        if (!trigger) return;
        
        // Skip comment delete buttons handled by custom ajax
        if (trigger.classList.contains('comment-delete-btn')) return;

        e.preventDefault();

        const title = trigger.dataset.confirmTitle || 'Kustutamise kinnitus';
        const message = trigger.dataset.confirmMessage || 'Kas oled kindel, et soovid selle elemendi kustutada? Seda tegevust ei saa tagasi võtta.';
        const href = trigger.getAttribute('href');

        showConfirmModal({
            title: title,
            message: message,
            confirmText: 'Kustuta',
            onConfirm: () => {
                if (href && href !== '#' && !href.startsWith('javascript:')) {
                    window.location.href = href;
                } else if (trigger.form) {
                    trigger.form.submit();
                }
            }
        });
    });
}

