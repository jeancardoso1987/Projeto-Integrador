document.addEventListener("DOMContentLoaded", () => {
    const servidorSelect = document.getElementById("servidorSelect");
    const listaMVPs = document.getElementById("listaMVPs");
    const monitorandoDiv = document.getElementById("monitorando");
    const listaMonitoramento = document.getElementById("listaMonitoramento");

    let servidorAtual = "";

    servidorSelect.addEventListener("change", async () => {
        servidorAtual = servidorSelect.options[servidorSelect.selectedIndex].text;
        const id = servidorSelect.value;
        if (!id) return;
        const res = await fetch(`api/get_mvps.php?servidor_id=${id}`);
        const mvps = await res.json();

        document.getElementById("lista-mvp").style.display = "block";

        listaMVPs.innerHTML = "";
        mvps.forEach(mvp => {
            const item = document.createElement("div");

            item.classList.add("mvp-card");
            item.innerHTML = `
                <img src="/MVP-TIMER/assets/mvps/${mvp.imagem}" alt="${mvp.nome}">
                <h3>${mvp.nome}</h3>
                <p>Mapa: ${mvp.mapa}</p>
                <p>Respawn: ${mvp.tempo_respawn} min</p>
                <label>Hora da morte:</label>
                <input type="time" class="horaMorte" data-nome="${mvp.nome}" data-tempo="${mvp.tempo_respawn}">
                <button class="adicionar">Monitora</button>
            `;
            listaMVPs.appendChild(item);
        });

        document.querySelectorAll(".adicionar").forEach(btn => {
            btn.addEventListener("click", e => {
                const card = e.target.closest(".mvp-card");
                const nome = card.querySelector("h3").innerText;
                const tempo = parseInt(card.querySelector(".horaMorte").dataset.tempo);
                const horaMorte = card.querySelector(".horaMorte").value;
                const imagem = card.querySelector("img").getAttribute("src");

                if (!horaMorte) {
                    alert("Informe a hora da morte!");
                    return;
                }

                const jaMonitorado = Array.from(listaMonitoramento.children).some(item => {
                    const nomeExistente = item.querySelector("h4").innerText;
                    const servidorExistente = item.querySelector(".servidor-nome")?.innerText;
                    return nomeExistente === nome && servidorExistente === servidorAtual;
                });
                if (jaMonitorado) {
                    alert(`O MVP "${nome}" já está sendo monitorado no servidor "${servidorAtual}"!`);
                    return;
                }

                const hora = new Date();
                const [h, m] = horaMorte.split(":");
                hora.setHours(h, m, 0);

                let tempoRestante = (tempo * 60000) - (Date.now() - hora.getTime());
                if (tempoRestante <= 0) {
                    alert("Esse MVP já deve ter renascido!");
                    return;
                }

                monitorandoDiv.style.display = "block";

                const mvpMon = document.createElement("div");
                mvpMon.classList.add("monitor-item");
                mvpMon.innerHTML = `
                    <img src="${imagem}" alt="${nome}" class="mvp-img" />
                    <h4>${nome}</h4>
                    <p class="servidor-nome">${servidorAtual}</p>
                    <p>Tempo restante: <span class="cronometro"></span></p>
                    <button class="parar">Parar</button>
                    <button class="reiniciar" style="display: none;">Reiniciar</button>
                `;

                listaMonitoramento.appendChild(mvpMon);

                let restante = tempoRestante / 1000;
                const cronometro = mvpMon.querySelector(".cronometro");
                const btnReiniciar = mvpMon.querySelector(".reiniciar");

                const timer = setInterval(() => {
                    if (restante <= 0) {
                        clearInterval(timer);
                        cronometro.textContent = "⚔️ MVP VIVO!";
                        cronometro.classList.remove("alerta-amarelo", "alerta-vermelho");
                        cronometro.classList.add("ativo");
                        new Audio("assets/sounds/alert.mp3").play();
                        btnReiniciar.style.display = "inline-block";
                    } else {
                        const min = Math.floor(restante / 60);
                        const sec = Math.floor(restante % 60);
                        cronometro.textContent = `${min}m ${sec < 10 ? "0" + sec : sec}s`;
                        cronometro.classList.remove("alerta-amarelo", "alerta-vermelho", "ativo");

                        if (restante <= 60) {
                            cronometro.classList.add("alerta-vermelho");
                        } else if (restante <= 300) {
                            cronometro.classList.add("alerta-amarelo");
                        }

                        restante--;
                    }
                }, 1000);

                mvpMon.querySelector(".parar").addEventListener("click", () => {
                    clearInterval(timer);
                    mvpMon.remove();
                    if (listaMonitoramento.children.length === 0) {
                        monitorandoDiv.style.display = "none";
                    }
                });

                btnReiniciar.addEventListener("click", () => {
                    const novaHora = new Date();
                    novaHora.setSeconds(0);

                    let tempoRestanteNovo = (tempo * 60000) - (Date.now() - novaHora.getTime());
                    if (tempoRestanteNovo <= 0) {
                        alert("Esse MVP já deve ter renascido!");
                        return;
                    }

                    restante = tempoRestanteNovo / 1000;
                    cronometro.textContent = `${Math.floor(restante / 60)}m ${Math.floor(restante % 60)}s`;

                    const novoTimer = setInterval(() => {
                        if (restante <= 0) {
                            clearInterval(novoTimer);
                            cronometro.textContent = "⚔️ MVP VIVO!";
                            cronometro.classList.add("ativo");
                            new Audio("assets/sounds/alert.mp3").play();
                            btnReiniciar.style.display = "inline-block";
                        } else {
                            const min = Math.floor(restante / 60);
                            const sec = Math.floor(restante % 60);
                            cronometro.textContent = `${min}m ${sec < 10 ? "0" + sec : sec}s`;
                            restante--;
                        }
                    }, 1000);

                    btnReiniciar.style.display = "none";
                });
            });
        });
    });
});
