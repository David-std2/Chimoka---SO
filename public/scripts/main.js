// public/scripts/main.js
document.addEventListener('DOMContentLoaded', () => {
  const APP = window.APP || {};
  const tabla = APP.tabla || '';
  const columns = APP.columns || [];
  const pk = APP.pk || null;

  const searchInput = document.getElementById('search');
  const tableBody = document.querySelector('#data-table tbody');
  const progressScroll = document.getElementById('progressScroll');
  const tw = document.querySelector('.table-wrap');
  const scrollInner = document.getElementById('scrollInner');

  // Live search
  if (searchInput && tableBody) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.trim().toLowerCase();
      for (const row of tableBody.rows) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
      }
    });
  }

// ===== Scroll progress bar sync =====
if (tw && scrollInner && progressScroll) {
  function updateProgressFromScroll() {
    const maxScroll = tw.scrollWidth - tw.clientWidth;
    const pct = (tw.scrollLeft / maxScroll) * 100;
    scrollInner.style.width = Math.min(Math.max(pct, 0), 100) + '%';
  }

  tw.addEventListener('scroll', updateProgressFromScroll);
  setTimeout(updateProgressFromScroll, 60);

  let dragging = false;

  function scrollToPosition(clientX) {
    const rect = progressScroll.getBoundingClientRect();
    const x = Math.max(0, Math.min(clientX - rect.left, rect.width));
    const scrollValue = (x / rect.width) * (tw.scrollWidth - tw.clientWidth);
    tw.scrollLeft = scrollValue;
    updateProgressFromScroll();
  }

  progressScroll.addEventListener('click', ev => scrollToPosition(ev.clientX));

  // Arrastre con mouse
  progressScroll.addEventListener('mousedown', ev => {
    dragging = true;
    document.body.classList.add('no-select');
    scrollToPosition(ev.clientX);
  });

  window.addEventListener('mousemove', ev => {
    if (!dragging) return;
    scrollToPosition(ev.clientX);
  });

  window.addEventListener('mouseup', () => {
    if (!dragging) return;
    dragging = false;
    document.body.classList.remove('no-select');
  });

  // Touch (movil)
  progressScroll.addEventListener('touchstart', ev => {
    dragging = true;
    scrollToPosition(ev.touches[0].clientX);
  }, { passive: true });

  window.addEventListener('touchmove', ev => {
    if (!dragging) return;
    scrollToPosition(ev.touches[0].clientX);
  }, { passive: true });

  window.addEventListener('touchend', () => dragging = false);
}


  // Modal view / create / edit / delete handlers
  const verBtns = document.querySelectorAll('.ver');
  const modalVerEl = document.getElementById('modalVer');
  const modalFormEl = document.getElementById('modalForm');
  const modalVer = modalVerEl ? new bootstrap.Modal(modalVerEl) : null;
  const modalForm = modalFormEl ? new bootstrap.Modal(modalFormEl) : null;

  verBtns.forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      const body = document.getElementById('modalVerBody');
      body.innerHTML = '<p class="text-muted text-center">Cargando...</p>';
      if (modalVer) modalVer.show();
      try {
        const res = await fetch('app/ajax/get_record.php?tabla=' + encodeURIComponent(tabla) + '&id=' + encodeURIComponent(id));
        if (res.ok) {
          const data = await res.json();
          if (data && data.row) {
            let html = '<div class="table-responsive"><table class="table table-sm">';
            for (const k in data.row) {
              html += `<tr><th style="width:35%">${k}</th><td>${String(data.row[k])}</td></tr>`;
            }
            html += '</table></div>';
            body.innerHTML = html;
            return;
          }
        }
      } catch (e) { /* fallback */ }

      const tr = btn.closest('tr');
      const cells = Array.from(tr.children).slice(0, -1);
      let html = '<div class="table-responsive"><table class="table table-sm">';
      cells.forEach((td, i) => {
        const col = columns[i] ?? ('col' + i);
        html += `<tr><th style="width:35%">${col}</th><td>${td.innerText}</td></tr>`;
      });
      html += '</table></div>';
      body.innerHTML = html;
    });
  });

  // new/edit/delete
  document.getElementById('btn-new')?.addEventListener('click', () => openFormModal('create'));
  document.querySelectorAll('.editar').forEach(btn => btn.addEventListener('click', () => openFormModal('edit', btn.dataset.id)));

  async function openFormModal(mode='create', id=null) {
    const titleEl = document.getElementById('modalFormTitle');
    const bodyEl = document.getElementById('modalFormBody');
    titleEl.textContent = mode === 'create' ? 'Nuevo registro' : 'Editar registro #' + id;
    let html = '<div class="row g-3">';
    let rowData = {};
    if (mode === 'edit' && id) {
      try {
        const res = await fetch('app/ajax/get_record.php?tabla=' + encodeURIComponent(tabla) + '&id=' + encodeURIComponent(id));
        if (res.ok) {
          const j = await res.json();
          if (j && j.row) rowData = j.row;
        }
      } catch (e) {
        const tr = document.querySelector(`.editar[data-id="${id}"]`)?.closest('tr');
        if (tr) {
          const cells = Array.from(tr.children).slice(0, -1);
          columns.forEach((c, idx) => rowData[c] = (cells[idx] ? cells[idx].innerText : ''));
        }
      }
    }
    if (!columns || columns.length === 0) {
      html += '<div class="col-12"><p class="text-muted">No hay esquema para esta tabla.</p></div>';
    } else {
      columns.forEach((c) => {
        html += `<div class="col-md-6"><label class="form-label">${c}</label>
                 <input name="${c}" class="form-control" value="${(rowData[c] ?? '')}"></div>`;
      });
    }
    html += '</div>';
    bodyEl.innerHTML = html;

    document.getElementById('formDynamic').onsubmit = async (ev) => {
      ev.preventDefault();
      const form = ev.target;
      const data = {};
      for (const el of form.elements) {
        if (!el.name) continue;
        data[el.name] = el.value;
      }
      try {
        const res = await fetch('app/ajax/save_record.php', {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({ tabla, mode, id, data })
        });
        if (res.ok) {
          const j = await res.json();
          if (j.success) {
            alert('Guardado correctamente.');
            if (modalForm) modalForm.hide();
            location.reload();
            return;
          } else {
            alert('Error: ' + (j.message || 'no guardado'));
            return;
          }
        }
      } catch (e) {
        alert('Simulado: endpoint save_record.php no disponible.');
        if (modalForm) modalForm.hide();
      }
    };

    if (modalForm) modalForm.show();
  }

  document.querySelectorAll('.eliminar').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      if (!confirm('¿Eliminar este registro?')) return;
      try {
        const res = await fetch('app/ajax/delete_record.php', {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({ tabla, id })
        });
        if (res.ok) {
          const j = await res.json();
          if (j.success) {
            alert('Eliminado.');
            location.reload();
            return;
          } else {
            alert('Respuesta: ' + (j.message || 'error'));
            return;
          }
        }
      } catch (e) {
        alert('Eliminación simulada (endpoint no disponible). Recarga para ver cambios reales.');
      }
    });
  });

  function updateStickySpace() {
    const table = document.querySelector('.table');
    const tw = document.querySelector('.table-wrap');
    if (!table || !tw) return;

    const stickyEls = table.querySelectorAll('.sticky-col');
    if (!stickyEls || stickyEls.length === 0) return;

    stickyEls.forEach(el => {
      el.style.right = '0px';
      el.style.zIndex = '45';
      el.style.background = el.closest('thead') ? 'var(--accent-dark)' : '#fff';
      el.style.boxShadow = '-8px 0 18px rgba(15,30,37,0.06)';
    });

    table.querySelectorAll('.spacer-col').forEach(el => el.remove());
    tw.style.paddingRight = '0px';
    if (typeof updateProgressFromScroll === 'function') updateProgressFromScroll();
  }

  updateStickySpace();
  let _tmoSticky;
  window.addEventListener('resize', () => {
    clearTimeout(_tmoSticky);
    _tmoSticky = setTimeout(updateStickySpace, 120);
  });
  if (window.MutationObserver) {
    const moSticky = new MutationObserver(() => updateStickySpace());
    const table = document.querySelector('.table');
    if (table) moSticky.observe(table, { childList: true, subtree: true, attributes: true });
  }

});
