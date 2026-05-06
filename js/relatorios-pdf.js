// js/relatorios-pdf.js — Geração do PDF do relatório

function exportarPDF() {
    const btn = document.getElementById('btnExportarPdf');
    btn.textContent = '⏳ Gerando...';
    btn.disabled = true;

    try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

        const LARANJA = [232, 160, 32];   // var(--accent)
        const CINZA   = [30,  33,  48];   // var(--bg2)
        const BRANCO  = [255, 255, 255];
        const TEXTO   = [200, 205, 220];
        const SUAVE   = [50,  54,  72];

        const PW = 210; // largura A4 em mm
        const ML = 14;  // margem esquerda
        const MR = 14;  // margem direita
        const CW = PW - ML - MR;

        // ── Fundo da página ──────────────────────────────────────────
        doc.setFillColor(...CINZA);
        doc.rect(0, 0, PW, 297, 'F');

        // ── Cabeçalho ────────────────────────────────────────────────
        doc.setFillColor(...LARANJA);
        doc.rect(0, 0, PW, 28, 'F');

        doc.setTextColor(...CINZA);
        doc.setFontSize(16);
        doc.setFont('helvetica', 'bold');
        doc.text('⚔ Ragnarok MVP Timer', ML, 12);

        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        doc.text('Relatório de Monitoramentos', ML, 19);

        // Data de geração (canto direito)
        const agora = new Date().toLocaleString('pt-BR');
        doc.text('Gerado em: ' + agora, PW - MR, 12, { align: 'right' });

        // ── Bloco de informações da conta ────────────────────────────
        let y = 36;

        doc.setFillColor(...SUAVE);
        doc.roundedRect(ML, y, CW, 26, 3, 3, 'F');

        doc.setTextColor(...LARANJA);
        doc.setFontSize(8);
        doc.setFont('helvetica', 'bold');
        doc.text('CONTA', ML + 4, y + 6);
        doc.text('PERÍODO', ML + 70, y + 6);
        doc.text('TOTAL DE REGISTROS', ML + 130, y + 6);

        doc.setTextColor(...TEXTO);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        doc.text(PDF_META.email,     ML + 4,   y + 13);
        doc.text('Criado em: ' + PDF_META.criado_em, ML + 4, y + 19);
        doc.text(PDF_META.de + ' até ' + PDF_META.ate, ML + 70, y + 13);
        doc.text(String(PDF_META.total), ML + 130, y + 13);

        y += 34;

        // ── Ranking ──────────────────────────────────────────────────
        if (PDF_META.ranking.length > 0) {
            doc.setTextColor(...LARANJA);
            doc.setFontSize(10);
            doc.setFont('helvetica', 'bold');
            doc.text('Ranking de MVPs mais monitorados', ML, y);
            y += 5;

            const rankingBody = PDF_META.ranking.map((r, i) => [
                i + 1,
                r.mvp,
                r.total + 'x',
            ]);

            doc.autoTable({
                startY: y,
                head: [['#', 'MVP', 'Monitoramentos']],
                body: rankingBody,
                margin: { left: ML, right: MR },
                styles: {
                    fontSize: 8,
                    cellPadding: 2.5,
                    textColor: TEXTO,
                    fillColor: SUAVE,
                    lineColor: [60, 65, 90],
                    lineWidth: 0.1,
                },
                headStyles: {
                    fillColor: LARANJA,
                    textColor: CINZA,
                    fontStyle: 'bold',
                    fontSize: 8,
                },
                alternateRowStyles: { fillColor: [40, 44, 62] },
                columnStyles: {
                    0: { cellWidth: 10, halign: 'center' },
                    2: { cellWidth: 35, halign: 'center' },
                },
            });

            y = doc.lastAutoTable.finalY + 8;
        }

        // ── Tabela de histórico ──────────────────────────────────────
        doc.setTextColor(...LARANJA);
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text('Histórico de monitoramentos', ML, y);
        y += 5;

        if (PDF_DADOS.length === 0) {
            doc.setTextColor(...TEXTO);
            doc.setFontSize(9);
            doc.setFont('helvetica', 'normal');
            doc.text('Nenhum registro encontrado para o período selecionado.', ML, y + 6);
        } else {
            const isAdmin = PDF_META.role === 'admin';
            const cabecalho = isAdmin
                ? ['Usuário', 'MVP', 'Servidor', 'Iniciado em', 'Morto em', 'Spawn est.', 'Status']
                : ['MVP', 'Servidor', 'Iniciado em', 'Morto em', 'Spawn est.', 'Status'];

            const corpo = PDF_DADOS.map(r => isAdmin
                ? [r.email, r.mvp, r.servidor, r.iniciado_em, r.morto_em, r.spawn_est, r.status]
                : [r.mvp, r.servidor, r.iniciado_em, r.morto_em, r.spawn_est, r.status]
            );

            doc.autoTable({
                startY: y,
                head: [cabecalho],
                body: corpo,
                margin: { left: ML, right: MR },
                styles: {
                    fontSize: 7.5,
                    cellPadding: 2.5,
                    textColor: TEXTO,
                    fillColor: SUAVE,
                    lineColor: [60, 65, 90],
                    lineWidth: 0.1,
                    overflow: 'ellipsize',
                },
                headStyles: {
                    fillColor: LARANJA,
                    textColor: CINZA,
                    fontStyle: 'bold',
                    fontSize: 8,
                },
                alternateRowStyles: { fillColor: [40, 44, 62] },
                didParseCell(data) {
                    // Colorir coluna Status
                    const isStatusCol = data.column.index === cabecalho.length - 1;
                    if (data.section === 'body' && isStatusCol) {
                        if (data.cell.raw === 'Finalizado') {
                            data.cell.styles.textColor = [46, 204, 113];
                        } else {
                            data.cell.styles.textColor = [109, 179, 242];
                        }
                    }
                },
                // Rodapé em cada página
                didDrawPage(data) {
                    const pg  = doc.internal.getCurrentPageInfo().pageNumber;
                    const tot = doc.internal.getNumberOfPages();
                    doc.setFillColor(...CINZA);
                    doc.rect(0, 285, PW, 12, 'F');
                    doc.setTextColor(100, 105, 130);
                    doc.setFontSize(7);
                    doc.setFont('helvetica', 'normal');
                    doc.text('Ragnarok MVP Timer — Relatório confidencial', ML, 291);
                    doc.text('Página ' + pg + ' de ' + tot, PW - MR, 291, { align: 'right' });
                },
            });
        }

        // Nome do arquivo com período
        const de  = PDF_META.de.replace(/\//g, '-');
        const ate = PDF_META.ate.replace(/\//g, '-');
        doc.save(`mvp-relatorio_${de}_${ate}.pdf`);

    } catch (e) {
        console.error('Erro ao gerar PDF:', e);
        alert('Erro ao gerar o PDF. Tente novamente.');
    } finally {
        btn.textContent = '⬇ Exportar PDF';
        btn.disabled = false;
    }
}