class Presentation {
    constructor() {
        this.slides = document.querySelectorAll('.slide');
        this.currentSlide = 1;
        this.totalSlides = this.slides.length;
        
        this.prevBtn = document.getElementById('prev-btn');
        this.nextBtn = document.getElementById('next-btn');
        this.currentSlideEl = document.getElementById('current-slide');
        this.progressFill = document.getElementById('progress-fill');
        
        this.init();
    }
    
    init() {
        this.updateUI();
        this.bindEvents();
        this.positionSlides();
    }
    
    bindEvents() {
        this.prevBtn.addEventListener('click', () => this.prev());
        this.nextBtn.addEventListener('click', () => this.next());
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') this.prev();
            if (e.key === 'ArrowRight') this.next();
        });
        
        let touchStartX = 0;
        document.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
        });
        document.addEventListener('touchend', (e) => {
            const diff = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) {
                diff > 0 ? this.next() : this.prev();
            }
        });
    }
    
    positionSlides() {
        this.slides.forEach((slide, index) => {
            const slideNum = index + 1;
            if (slideNum < this.currentSlide) {
                slide.style.transform = 'translateX(-100%)';
                slide.style.opacity = '0';
                slide.classList.remove('active');
            } else if (slideNum === this.currentSlide) {
                slide.style.transform = 'translateX(0)';
                slide.style.opacity = '1';
                slide.classList.add('active');
            } else {
                slide.style.transform = 'translateX(100%)';
                slide.style.opacity = '0';
                slide.classList.remove('active');
            }
        });
    }
    
    goTo(slideNumber) {
        if (slideNumber < 1 || slideNumber > this.totalSlides) return;
        this.currentSlide = slideNumber;
        this.positionSlides();
        this.updateUI();
        this.triggerAnimations();
    }
    
    next() {
        if (this.currentSlide < this.totalSlides) {
            this.goTo(this.currentSlide + 1);
        }
    }
    
    prev() {
        if (this.currentSlide > 1) {
            this.goTo(this.currentSlide - 1);
        }
    }
    
    updateUI() {
        this.currentSlideEl.textContent = this.currentSlide;
        this.progressFill.style.width = `${(this.currentSlide / this.totalSlides) * 100}%`;
        this.prevBtn.disabled = this.currentSlide === 1;
        this.nextBtn.disabled = this.currentSlide === this.totalSlides;
    }
    
    triggerAnimations() {
        const animatedElements = document.querySelectorAll('.slide.active .fade-in');
        animatedElements.forEach((el) => {
            el.style.animation = 'none';
            void el.offsetHeight;
            el.style.animation = '';
        });
    }
}

/* SHA-256 helper */
async function sha256(message) {
    const encoder = new TextEncoder();
    const data = encoder.encode(message);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

/* Intro buttons */
function initIntroButtons() {
    const label = document.getElementById('intro-label');
    document.querySelectorAll('.intro-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const reveal = btn.dataset.reveal;
            const texts = {
                hash: 'Hash',
                block: 'Bloque',
                blockchain: 'Blockchain'
            };
            label.textContent = texts[reveal] || '';
        });
    });
}

/* Hash demo */
function initHashDemo() {
    const dataInput = document.getElementById('hash-data');
    const resultInput = document.getElementById('hash-result');
    if (!dataInput || !resultInput) return;
    
    async function updateHash() {
        const text = dataInput.value;
        if (!text) {
            resultInput.value = '';
            return;
        }
        const hash = await sha256(text);
        resultInput.value = hash;
    }
    
    dataInput.addEventListener('input', updateHash);
}

/* Single Block demo */
function initBlockDemo() {
    const blockEl = document.getElementById('single-block');
    const numberInput = document.getElementById('sb-number');
    const nonceInput = document.getElementById('sb-nonce');
    const dataInput = document.getElementById('sb-data');
    const hashInput = document.getElementById('sb-hash');
    const mineBtn = document.getElementById('sb-mine');
    const statusEl = blockEl.querySelector('.block-status');
    
    if (!blockEl) return;
    
    let minedHash = '';
    
    async function computeHash() {
        const str = numberInput.value + '|' + nonceInput.value + '|' + dataInput.value;
        return await sha256(str);
    }
    
    async function updateHashDisplay() {
        const hash = await computeHash();
        hashInput.value = hash;
        updateBlockState();
    }
    
    function updateBlockState() {
        const currentHash = hashInput.value;
        if (!minedHash) {
            blockEl.classList.remove('mined', 'modified');
            statusEl.textContent = 'Sin minar';
            return;
        }
        if (currentHash === minedHash) {
            blockEl.classList.add('mined');
            blockEl.classList.remove('modified');
            statusEl.textContent = 'Minado';
        } else {
            blockEl.classList.remove('mined');
            blockEl.classList.add('modified');
            statusEl.textContent = 'Modificado';
        }
    }
    
    async function mineBlock() {
        const targetPrefix = '0000';
        let nonce = parseInt(nonceInput.value) || 0;
        const originalNonce = nonce;
        
        // Simple proof of work: find nonce making hash start with 0000
        let hash = await computeHash();
        if (!hash.startsWith(targetPrefix)) {
            mineBtn.disabled = true;
            mineBtn.textContent = '⛏ Minando...';
            
            await new Promise(resolve => setTimeout(resolve, 10));
            
            let attempts = 0;
            while (!hash.startsWith(targetPrefix) && attempts < 100000) {
                nonce++;
                nonceInput.value = nonce;
                hash = await sha256(numberInput.value + '|' + nonce + '|' + dataInput.value);
                attempts++;
                if (attempts % 500 === 0) {
                    await new Promise(r => setTimeout(r, 0));
                }
            }
            
            hashInput.value = hash;
            mineBtn.disabled = false;
            mineBtn.textContent = '⛏ Minar';
        }
        
        minedHash = hash;
        updateBlockState();
    }
    
    [numberInput, nonceInput, dataInput].forEach(input => {
        input.addEventListener('input', updateHashDisplay);
    });
    
    mineBtn.addEventListener('click', mineBlock);
    
    // Initial compute
    updateHashDisplay().then(() => {
        // Initially mined with default values
        minedHash = hashInput.value;
        updateBlockState();
    });
}

/* Blockchain demo */
function initBlockchainDemo() {
    const container = document.getElementById('blockchain-container');
    if (!container) return;
    
    const BLOCK_COUNT = 5;
    const GENESIS_PREV = '0'.repeat(64);
    
    // Generate HTML for blocks
    let html = '';
    for (let i = 1; i <= BLOCK_COUNT; i++) {
        html += `
            <div class="block-card chain-block mined" id="cb-${i}" data-index="${i}">
                <div class="block-header">
                    <span class="block-title">&#128230; Bloque #${i}</span>
                    <span class="block-status">Minado</span>
                </div>
                <div class="block-inputs">
                    <div class="input-row">
                        <span class="block-label">Bloque</span>
                        <input type="number" class="block-input small cb-number" value="${i}" min="1" data-index="${i}">
                    </div>
                    <div class="input-row">
                        <span class="block-label">Nonce</span>
                        <input type="text" class="block-input small cb-nonce" value="0" inputmode="numeric" pattern="[0-9]*" data-index="${i}">
                    </div>
                    <div class="input-row full-width">
                        <span class="block-label">Datos</span>
                        <textarea class="block-input cb-data" rows="2" placeholder="Datos del bloque..." data-index="${i}">${i === 1 ? 'Genesis' : 'Transacciones bloque ' + i}</textarea>
                    </div>
                    <div class="input-row full-width">
                        <span class="block-label">Anterior</span>
                        <textarea class="block-input cb-prev" readonly rows="3" data-index="${i}"></textarea>
                    </div>
                    <div class="input-row full-width">
                        <span class="block-label">Hash</span>
                        <textarea class="block-input cb-hash" readonly rows="3" data-index="${i}"></textarea>
                    </div>
                    <div class="input-row full-width" style="text-align: center;">
                        <button class="mine-btn cb-mine" data-index="${i}">&#9935; Minar</button>
                    </div>
                </div>
            </div>
        `;
        if (i < BLOCK_COUNT) {
            html += `<div class="chain-link">&#8595;</div>`;
        }
    }
    container.innerHTML = html;
    
    // State
    const blocks = [];
    for (let i = 1; i <= BLOCK_COUNT; i++) {
        blocks.push({
            index: i,
            minedHash: '',
            el: document.getElementById(`cb-${i}`),
            numberInput: document.querySelector(`.cb-number[data-index="${i}"]`),
            nonceInput: document.querySelector(`.cb-nonce[data-index="${i}"]`),
            dataInput: document.querySelector(`.cb-data[data-index="${i}"]`),
            prevInput: document.querySelector(`.cb-prev[data-index="${i}"]`),
            hashInput: document.querySelector(`.cb-hash[data-index="${i}"]`),
            mineBtn: document.querySelector(`.cb-mine[data-index="${i}"]`),
            statusEl: document.querySelector(`#cb-${i} .block-status`)
        });
    }
    
    async function computeBlockHash(blockIndex) {
        const b = blocks[blockIndex - 1];
        const prev = b.prevInput.value || '';
        const str = prev + '|' + b.numberInput.value + '|' + b.nonceInput.value + '|' + b.dataInput.value;
        return await sha256(str);
    }
    
    async function recalcChain(fromIndex) {
        for (let i = fromIndex; i <= BLOCK_COUNT; i++) {
            const b = blocks[i - 1];
            if (i > 1) {
                const prevHash = blocks[i - 2].hashInput.value;
                b.prevInput.value = prevHash;
            } else {
                b.prevInput.value = GENESIS_PREV;
            }
            const hash = await computeBlockHash(i);
            b.hashInput.value = hash;
        }
    }
    
    function updateBlockVisuals() {
        for (let i = 1; i <= BLOCK_COUNT; i++) {
            const b = blocks[i - 1];
            const currentHash = b.hashInput.value;
            const el = b.el;
            
            if (!b.minedHash) {
                el.classList.remove('mined', 'modified');
                b.statusEl.textContent = 'Sin minar';
                continue;
            }
            
            if (currentHash === b.minedHash) {
                el.classList.add('mined');
                el.classList.remove('modified');
                b.statusEl.textContent = 'Minado';
            } else {
                el.classList.remove('mined');
                el.classList.add('modified');
                b.statusEl.textContent = 'Modificado';
            }
        }
    }
    
    async function mineBlock(blockIndex) {
        const b = blocks[blockIndex - 1];
        const targetPrefix = '0000';
        let nonce = parseInt(b.nonceInput.value) || 0;
        
        let hash = b.hashInput.value;
        if (!hash.startsWith(targetPrefix)) {
            b.mineBtn.disabled = true;
            b.mineBtn.textContent = '⛏ Minando...';
            
            await new Promise(r => setTimeout(r, 10));
            
            let attempts = 0;
            while (!hash.startsWith(targetPrefix) && attempts < 100000) {
                nonce++;
                b.nonceInput.value = nonce;
                hash = await sha256(
                    b.prevInput.value + '|' + b.numberInput.value + '|' + nonce + '|' + b.dataInput.value
                );
                attempts++;
                if (attempts % 500 === 0) {
                    await new Promise(r => setTimeout(r, 0));
                }
            }
            
            b.hashInput.value = hash;
            b.mineBtn.disabled = false;
            b.mineBtn.textContent = '⛏ Minar';
        }
        
        b.minedHash = hash;
        
        // Propagate to next blocks
        if (blockIndex < BLOCK_COUNT) {
            blocks[blockIndex].prevInput.value = hash;
            await recalcChain(blockIndex + 1);
        }
        
        updateBlockVisuals();
    }
    
    function attachListeners() {
        for (let i = 1; i <= BLOCK_COUNT; i++) {
            const b = blocks[i - 1];
            const inputs = [b.numberInput, b.nonceInput, b.dataInput];
            
            inputs.forEach(input => {
                input.addEventListener('input', async () => {
                    await recalcChain(i);
                    updateBlockVisuals();
                });
            });
            
            b.mineBtn.addEventListener('click', () => mineBlock(i));
        }
    }
    
    // Initialize
    async function init() {
        for (let i = 1; i <= BLOCK_COUNT; i++) {
            const b = blocks[i - 1];
            if (i === 1) {
                b.prevInput.value = GENESIS_PREV;
            } else {
                b.prevInput.value = blocks[i - 2].hashInput.value;
            }
            const hash = await computeBlockHash(i);
            b.hashInput.value = hash;
            b.minedHash = hash;
        }
        updateBlockVisuals();
    }
    
    attachListeners();
    init();
}

/* Network nodes visual */
function initNetworkVisual() {
    const container = document.getElementById('network-visual');
    if (!container) return;

    const nodeCount = 15;
    const icons = ['&#128187;', '&#128421;', '&#128424;', '&#127758;', '&#128295;'];
    let html = '';
    for (let i = 0; i < nodeCount; i++) {
        const icon = icons[i % icons.length];
        const delay = (i * 0.15).toFixed(2);
        html += `<div class="node" style="animation-delay: ${delay}s">${icon}</div>`;
    }
    container.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', () => {
    new Presentation();
    initIntroButtons();
    initHashDemo();
    initBlockDemo();
    initBlockchainDemo();
    initNetworkVisual();
});
