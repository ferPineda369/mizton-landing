/**
 * KIMEN Buy Widget — Integración con smart contract
 * 
 * Dependencias: ethers.js v6 (CDN)
 * Contrato: KIMENVestingWithReserve
 * Red: BSC Mainnet (chainId 56)
 */

const KIMEN_CONFIG = {
    chainId: 56,
    chainName: 'BNB Smart Chain',
    rpcUrl: 'https://bsc-dataseed.binance.org/',
    vestingContract: '0x27AD8520c8623C3e43cDeE7CDDb8042A63cCB427',
    usdtAddress: '0x55d398326f99059fF775485246999027B3197955', // BSC USDT
    tokenPrice: 25, // USDT por token
    explorerUrl: 'https://bscscan.com'
};

const VESTING_ABI = [
    "function buyTokens(uint256 tokenAmount) external",
    "function saleActive() view returns (bool)",
    "function TOKEN_PRICE() view returns (uint256)"
];

const ERC20_ABI = [
    "function approve(address spender, uint256 amount) external returns (bool)",
    "function allowance(address owner, address spender) view returns (uint256)",
    "function balanceOf(address account) view returns (uint256)",
    "function decimals() view returns (uint8)"
];

// State
let provider = null;
let signer = null;
let userAddress = null;
let vestingContract = null;
let usdtContract = null;

// DOM Elements
const $ = (id) => document.getElementById(id);

// ─── Initialize ────────────────────────────────────────────────────────────────

function initBuyWidget() {
    const connectBtn = $('btn-connect-wallet');
    const amountInput = $('buy-amount');
    const approveBtn = $('btn-approve');
    const buyBtn = $('btn-buy');

    if (connectBtn) connectBtn.addEventListener('click', connectWallet);
    if (amountInput) amountInput.addEventListener('input', updateCost);
    if (approveBtn) approveBtn.addEventListener('click', approveUSDT);
    if (buyBtn) buyBtn.addEventListener('click', buyTokens);

    // Check if wallet already connected (wait for MetaMask to be ready)
    checkAutoConnect();
}

async function checkAutoConnect() {
    // Wait for ethereum provider to be ready
    if (typeof window.ethereum !== 'undefined') {
        try {
            // Get accounts without requesting (returns empty if not connected)
            const accounts = await window.ethereum.request({ 
                method: 'eth_accounts' 
            });
            
            if (accounts && accounts.length > 0) {
                // Wallet is already connected, auto-connect silently
                connectWallet().catch(err => {
                    console.warn('Auto-connect failed:', err);
                });
            }
        } catch (err) {
            console.warn('Failed to check wallet connection:', err);
        }
    }
}

// ─── Connect Wallet ────────────────────────────────────────────────────────────

async function connectWallet() {
    if (!window.ethereum) {
        showStatus('Instala MetaMask o una wallet compatible para continuar.', 'error');
        return;
    }

    const isAlreadyConnected = window.ethereum.selectedAddress;
    const connectBtn = $('btn-connect-wallet');
    
    // Only show UI feedback if user clicked (not auto-connect)
    if (!isAlreadyConnected && connectBtn) {
        connectBtn.disabled = true;
        connectBtn.textContent = 'Conectando...';
        showStatus('Conectando wallet...', 'info');
    }

    try {

        provider = new ethers.BrowserProvider(window.ethereum);
        const accounts = await provider.send('eth_requestAccounts', []);
        userAddress = accounts[0];
        signer = await provider.getSigner();

        // Verify network
        const network = await provider.getNetwork();
        if (Number(network.chainId) !== KIMEN_CONFIG.chainId) {
            await switchToBSC();
            // Re-initialize after network switch
            provider = new ethers.BrowserProvider(window.ethereum);
            signer = await provider.getSigner();
        }

        // Init contracts
        vestingContract = new ethers.Contract(KIMEN_CONFIG.vestingContract, VESTING_ABI, signer);
        usdtContract = new ethers.Contract(KIMEN_CONFIG.usdtAddress, ERC20_ABI, signer);

        // Check if sale is active
        const saleActive = await vestingContract.saleActive();
        if (!saleActive) {
            showStatus('La venta no está activa en este momento.', 'error');
            throw new Error('Sale not active');
        }

        // Update UI
        $('buy-wallet-address').textContent = userAddress.slice(0, 6) + '...' + userAddress.slice(-4);
        
        const balance = await usdtContract.balanceOf(userAddress);
        const decimals = await usdtContract.decimals();
        const balanceFormatted = ethers.formatUnits(balance, decimals);
        $('buy-usdt-balance').textContent = parseFloat(balanceFormatted).toFixed(2);

        // Show widget, hide connect button (only on success)
        $('buy-no-wallet').style.display = 'none';
        $('buy-widget').style.display = 'block';

        // Only show success message if user manually connected
        if (!isAlreadyConnected) {
            showStatus('Wallet conectada correctamente.', 'success');
        }
        updateCost();

    } catch (err) {
        console.error('Connect error:', err);
        
        // Restore button state on error
        const connectBtn = $('btn-connect-wallet');
        if (connectBtn) {
            connectBtn.disabled = false;
            connectBtn.textContent = '🦊 CONECTAR WALLET — OBTENER KIMEN $25';
        }
        
        // Keep widget hidden on error
        $('buy-no-wallet').style.display = 'block';
        $('buy-widget').style.display = 'none';
        
        if (err.code === 4001) {
            showStatus('Conexión rechazada por el usuario.', 'error');
        } else if (err.message === 'Sale not active') {
            // Already showed status above
        } else {
            showStatus('Error al conectar: ' + (err.shortMessage || err.message), 'error');
        }
    }
}

// ─── Switch to BSC ─────────────────────────────────────────────────────────────

async function switchToBSC() {
    try {
        await window.ethereum.request({
            method: 'wallet_switchEthereumChain',
            params: [{ chainId: '0x' + KIMEN_CONFIG.chainId.toString(16) }]
        });
    } catch (switchError) {
        // Chain not added — add it
        if (switchError.code === 4902) {
            await window.ethereum.request({
                method: 'wallet_addEthereumChain',
                params: [{
                    chainId: '0x' + KIMEN_CONFIG.chainId.toString(16),
                    chainName: KIMEN_CONFIG.chainName,
                    nativeCurrency: { name: 'BNB', symbol: 'BNB', decimals: 18 },
                    rpcUrls: [KIMEN_CONFIG.rpcUrl],
                    blockExplorerUrls: [KIMEN_CONFIG.explorerUrl]
                }]
            });
        } else {
            throw switchError;
        }
    }
}

// ─── Update Cost ───────────────────────────────────────────────────────────────

function updateCost() {
    const amount = parseInt($('buy-amount').value) || 0;
    const cost = amount * KIMEN_CONFIG.tokenPrice;
    $('buy-cost').textContent = cost.toLocaleString();

    // Enable/disable approve based on valid amount
    const approveBtn = $('btn-approve');
    if (approveBtn) approveBtn.disabled = amount < 1;
}

// ─── Approve USDT ──────────────────────────────────────────────────────────────

async function approveUSDT() {
    const amount = parseInt($('buy-amount').value) || 0;
    if (amount < 1) {
        showStatus('Ingresa al menos 1 token.', 'error');
        return;
    }

    const cost = BigInt(amount) * ethers.parseUnits(String(KIMEN_CONFIG.tokenPrice), 18);

    try {
        // Check existing allowance
        const currentAllowance = await usdtContract.allowance(userAddress, KIMEN_CONFIG.vestingContract);
        if (currentAllowance >= cost) {
            showStatus('USDT ya aprobado. Procede a comprar.', 'success');
            $('btn-approve').disabled = true;
            $('btn-buy').disabled = false;
            return;
        }

        showStatus('Aprobando USDT... Confirma en tu wallet.', 'info');
        setButtonsLoading(true);

        const tx = await usdtContract.approve(KIMEN_CONFIG.vestingContract, cost);
        showStatus(`Tx enviada: ${txLink(tx.hash)}. Esperando confirmación...`, 'info');

        await tx.wait();

        showStatus('USDT aprobado correctamente.', 'success');
        $('btn-approve').disabled = true;
        $('btn-buy').disabled = false;

    } catch (err) {
        console.error('Approve error:', err);
        handleTxError(err, 'aprobar USDT');
    } finally {
        setButtonsLoading(false);
    }
}

// ─── Buy Tokens ────────────────────────────────────────────────────────────────

async function buyTokens() {
    const amount = parseInt($('buy-amount').value) || 0;
    if (amount < 1) {
        showStatus('Ingresa al menos 1 token.', 'error');
        return;
    }

    // tokenAmount en unidades del contrato (con 18 decimales)
    const tokenAmount = ethers.parseUnits(String(amount), 18);

    try {
        showStatus('Enviando compra... Confirma en tu wallet.', 'info');
        setButtonsLoading(true);

        const tx = await vestingContract.buyTokens(tokenAmount);
        showStatus(`Tx enviada: ${txLink(tx.hash)}. Esperando confirmación...`, 'info');

        await tx.wait();

        // Show success
        $('buy-step-action').style.display = 'none';
        $('buy-step-amount').style.display = 'none';
        $('buy-step-done').style.display = 'block';
        $('buy-tx-link').href = `${KIMEN_CONFIG.explorerUrl}/tx/${tx.hash}`;

        showStatus('', 'hidden');

    } catch (err) {
        console.error('Buy error:', err);
        handleTxError(err, 'comprar tokens');
    } finally {
        setButtonsLoading(false);
    }
}

// ─── Helpers ───────────────────────────────────────────────────────────────────

function showStatus(msg, type) {
    const el = $('buy-status');
    if (!el) return;
    if (type === 'hidden') { el.style.display = 'none'; return; }
    el.style.display = 'block';
    el.className = 'buy-status buy-status--' + type;
    el.innerHTML = msg;
}

function txLink(hash) {
    const short = hash.slice(0, 10) + '...' + hash.slice(-6);
    return `<a href="${KIMEN_CONFIG.explorerUrl}/tx/${hash}" target="_blank" rel="noopener">${short}</a>`;
}

function setButtonsLoading(loading) {
    const approveBtn = $('btn-approve');
    const buyBtn = $('btn-buy');
    if (approveBtn) approveBtn.disabled = loading;
    if (buyBtn) buyBtn.disabled = loading;
}

function handleTxError(err, action) {
    if (err.code === 'ACTION_REJECTED' || err.code === 4001) {
        showStatus('Transacción rechazada por el usuario.', 'error');
    } else if (err.reason) {
        showStatus(`Error al ${action}: ${err.reason}`, 'error');
    } else {
        showStatus(`Error al ${action}: ${err.shortMessage || err.message}`, 'error');
    }
}

// ─── Listen for account/network changes ────────────────────────────────────────

if (window.ethereum) {
    window.ethereum.on('accountsChanged', () => window.location.reload());
    window.ethereum.on('chainChanged', () => window.location.reload());
}

// ─── Init on DOM ready ─────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', initBuyWidget);
