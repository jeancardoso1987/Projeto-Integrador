const API    = 'api/monitoramentos.php';
const timers = {};
let   todosMvps  = [];
let   mvpSelecionado = null;
let   sidAtual = null;

const ICON = {
    clock:  '<svg class="ico-mini" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    plus:   '<svg class="ico-mini" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
    x:      '<svg class="ico-mini" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
    sword:  '<svg class="ico-mini" viewBox="0 0 24 24"><polyline points="14.5 17.5 3 6 3 3 6 3 17.5 14.5"/><line x1="13" y1="19" x2="19" y2="13"/><line x1="16" y1="16" x2="20" y2="20"/><line x1="19" y1="21" x2="21" y2="19"/></svg>',
    skull:  '<svg class="ico-mini" viewBox="0 0 24 24"><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><path d="M8 20v2h8v-2"/><path d="m12.5 17-.5-1-.5 1h1z"/><path d="M16 20a2 2 0 0 0 1.56-3.25 8 8 0 1 0-11.12 0A2 2 0 0 0 8 20"/></svg>',
    rotate: '<svg class="ico-mini" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>',
};

document.getElementById('servidorSelect').addEventListener('change', function () {
    sidAtual = this.value;
    const listaEl = document.getElementById('lista-mvp');
    const mvpsEl  = document.getElementById('listaMVPs');
    const buscaEl = document.getElementById('buscaMvp');

    if (!sidAtual) {
        listaEl.style.display = 'none';
        mvpsEl.innerHTML = '';
        todosMvps = [];
        buscaEl.value = '';
        return;
    }

    mvpsEl.innerHTML = '<p class="carregando">Carregando…</p>';
    listaEl.style.display = '';
    buscaEl.value = '';

    fetch(`${API}?acao=listar_mvps&servidor_id=${sidAtual}`)
        .then(r => r.json())
        .then(mvps => {
            todosMvps = mvps;
            renderMVPs(mvps);
        })
        .catch(() => { mvpsEl.innerHTML = '<p class="erro">Erro ao carregar MVPs.</p>'; });
});

document.getElementById('buscaMvp').addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    if (!q) { renderMVPs(todosMvps); return; }
    renderMVPs(todosMvps.filter(m => m.nome.toLowerCase().includes(q) || m.mapa.toLowerCase().includes(q)));
});

function renderMVPs(mvps) {
    const el = document.getElementById('listaMVPs');
    if (!mvps.length) {
        el.innerHTML = '<p class="vazio">Nenhum MVP encontrado.</p>';
        return;
    }

    el.innerHTML = mvps.map(m => `
        <div class="mvp-card" id="mvpcard-${m.id}">
            <div class="mvp-card-img">
                ${m.img
                    ? `<img src="${m.img}" alt="${escHtml(m.nome)}" onerror="this.style.display='none'">`
                    : `<span class="sem-img-card">?</span>`}
            </div>
            <div class="mvp-card-info">
                <strong>${escHtml(m.nome)}</strong>
                <small>${escHtml(m.mapa)}</small>
                <small>${ICON.clock} ${m.spawn_min} min</small>
                ${m.nivel ? `<small>Lv ${m.nivel}${m.elemento ? ' · ' + escHtml(m.elemento) : ''}</small>` : ''}
            </div>
            <button class="btn-monitorar" onclick='abrirModal(${JSON.stringify(m)})'>
                ${ICON.plus} Monitorar
            </button>
        </div>
    `).join('');
}

function abrirModal(mvp) {
    mvpSelecionado = mvp;

    document.getElementById('modalMvpNome').textContent = mvp.nome;
    document.getElementById('modalMvpMapa').innerHTML = escHtml(mvp.mapa) + ' — ' + ICON.clock + ' ' + mvp.spawn_min + ' min';

    const imgEl = document.getElementById('modalImg');
    imgEl.innerHTML = mvp.img
        ? `<img src="${mvp.img}" alt="${escHtml(mvp.nome)}" onerror="this.style.display='none'">`
        : `<span class="sem-img-ativo">?</span>`;

    const agora = new Date();
    document.getElementById('modalHoraMorte').value =
        pad(agora.getHours()) + ':' + pad(agora.getMinutes());

    atualizarPreviewSpawn();

    document.getElementById('modalMorte').style.display = 'flex';
    document.getElementById('modalHoraMorte').focus();
}

function fecharModal() {
    document.getElementById('modalMorte').style.display = 'none';
    mvpSelecionado = null;
}

document.getElementById('modalMorte').addEventListener('click', function (e) {
    if (e.target === this) fecharModal();
});

document.getElementById('modalHoraMorte').addEventListener('input', atualizarPreviewSpawn);

function atualizarPreviewSpawn() {
    if (!mvpSelecionado) return;
    const horaInput = document.getElementById('modalHoraMorte').value;
    const preview   = document.getElementById('modalSpawnPreview');

    const mortoEm = horaParaDate(horaInput);
    const spawnEm = new Date(mortoEm.getTime() + mvpSelecionado.spawn_min * 60000);

    preview.innerHTML = `
        <div class="preview-linha">
            <span>${ICON.skull} Morte</span>
            <strong>${pad(mortoEm.getHours())}:${pad(mortoEm.getMinutes())}</strong>
        </div>
        <div class="preview-linha">
            <span>${ICON.rotate} Spawn estimado</span>
            <strong class="preview-spawn">${pad(spawnEm.getHours())}:${pad(spawnEm.getMinutes())}</strong>
        </div>
    `;
}

function horaParaDate(horaStr) {
    const agora = new Date();
    if (!horaStr) return agora;
    const [h, m] = horaStr.split(':').map(Number);
    const d = new Date();
    d.setHours(h, m, 0, 0);
    if (d > agora) d.setDate(d.getDate() - 1);
    return d;
}

function confirmarMonitor() {
    if (!mvpSelecionado || !sidAtual) return;

    const horaInput  = document.getElementById('modalHoraMorte').value;
    const mortoEmDt  = horaParaDate(horaInput);
    const spawnEstDt = new Date(mortoEmDt.getTime() + mvpSelecionado.spawn_min * 60000);

    const mortoEmStr  = dtParaMySQL(mortoEmDt);
    const spawnEstStr = dtParaMySQL(spawnEstDt);

    const btn = document.getElementById('btnConfirmarModal');
    btn.disabled = true;
    btn.textContent = 'Aguarde…';

    const fd = new FormData();
    fd.append('acao',        'iniciar');
    fd.append('mvp_id',      mvpSelecionado.id);
    fd.append('servidor_id', sidAtual);
    fd.append('morto_em',    mortoEmStr);
    fd.append('spawn_est',   spawnEstStr);

    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.erro) { alert(res.erro); return; }
            const mvp = mvpSelecionado;
            fecharModal();
            adicionarAtivoDOM(
                res.monitoramento_id,
                mvp.nome,
                mvp.mapa,
                mvp.spawn_min,
                spawnEstStr,
                mvp.img
            );
            atualizarContadorAtivos(1);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = ICON.sword + ' Iniciar monitoramento';
        });
}

function adicionarAtivoDOM(mid, nome, mapa, spawn, spawnEst, img = '') {
    const lista = document.getElementById('listaMonitoramento');
    const div   = document.createElement('div');
    div.className      = 'mvp-ativo';
    div.id             = `ativo-${mid}`;
    div.dataset.id     = mid;
    div.dataset.spawn  = spawn;
    div.dataset.mortoEm = spawnEst ?? '';

    div.innerHTML = `
        <div class="mvp-ativo-img">
            ${img
                ? `<img src="${img}" alt="${escHtml(nome)}" onerror="this.style.display='none'">`
                : `<span class="sem-img-ativo">?</span>`}
        </div>
        <div class="mvp-info">
            <strong>${escHtml(nome)}</strong>
            <small>${escHtml(mapa)}</small>
        </div>
        <div class="mvp-timer" id="timer-${mid}">--:--</div>
        <button class="btn-finalizar" onclick="finalizarMonitor(${mid})" title="Remover">${ICON.x}</button>
    `;
    lista.prepend(div);
    document.getElementById('monitorando').style.display = 'block';

    if (spawnEst) iniciarContagem(mid, spawnEst);
}

function iniciarContagem(mid, spawnEst) {
    const timerEl = document.getElementById(`timer-${mid}`);
    if (!timerEl) return;

    const alvo = new Date(spawnEst.replace(' ', 'T')).getTime();
    if (timers[mid]) clearInterval(timers[mid]);

    timers[mid] = setInterval(() => {
        const diff = alvo - Date.now();

        if (diff <= 0) {
            clearInterval(timers[mid]);
            timerEl.textContent = 'SPAWN!';
            timerEl.classList.add('timer-spawn');
            const nomeEl = document.querySelector(`#ativo-${mid} strong`);
            notificar((nomeEl?.textContent ?? 'MVP') + ' vai aparecer agora!');
            return;
        }

        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        timerEl.textContent = h > 0
            ? `${pad(h)}:${pad(m)}:${pad(s)}`
            : `${pad(m)}:${pad(s)}`;

        timerEl.classList.remove('timer-spawn');

        if (diff <= 300000 && diff > 295000) {
            const nomeEl = document.querySelector(`#ativo-${mid} strong`);
            notificar((nomeEl?.textContent ?? 'MVP') + ' vai spawnar em 5 minutos!');
        }
    }, 1000);
}

function finalizarMonitor(mid) {
    const fd = new FormData();
    fd.append('acao', 'finalizar');
    fd.append('monitoramento_id', mid);

    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.erro) { alert(res.erro); return; }
            clearInterval(timers[mid]);
            delete timers[mid];
            document.getElementById(`ativo-${mid}`)?.remove();
            atualizarContadorAtivos(-1);
            if (!document.querySelector('.mvp-ativo')) {
                document.getElementById('monitorando').style.display = 'none';
            }
        });
}

function atualizarContadorAtivos(delta) {
    const el = document.getElementById('statAtivos');
    if (el) el.textContent = Math.max(0, parseInt(el.textContent || '0') + delta);
}

function notificar(msg) {
    if ('Notification' in window) {
        if (Notification.permission === 'granted') {
            new Notification('⚔️ MVP Timer', { body: msg });
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission();
        }
    }
}

const pad      = n => String(n).padStart(2, '0');
const escHtml  = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const dtParaMySQL = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:00`;

if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

document.querySelectorAll('.mvp-ativo').forEach(el => {
    const mid     = el.dataset.id;
    const spawnEst = el.dataset.mortoEm;
    if (spawnEst) iniciarContagem(mid, spawnEst);
});
