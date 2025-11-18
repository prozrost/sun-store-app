<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-900">
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-semibold">Products</h1>

    <form id="products-filter" class="mt-4 flex flex-wrap items-center gap-3" method="get" action="{{ route('products.index') }}">
        <div class="flex items-center space-x-2">
            <input name="q" value="" placeholder="Search" class="border rounded px-2 py-1 w-64" />
        </div>

        <div class="flex items-center space-x-2">
            <label class="text-sm">Type</label>
            <select name="type" class="border rounded px-2 py-1">
                @foreach (App\Enums\ProductType::values() as $t)
                    <option value="{{ $t }}" {{ $t === 'batteries' ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center space-x-2">
            <label class="text-sm">Manufacturer</label>
            <select name="manufacturer" class="border rounded px-2 py-1">
                <option value="">All manufacturers</option>
            </select>
        </div>

        <div class="flex items-center space-x-2">
            <label class="text-sm">Price</label>
            <input name="price_from" type="number" step="0.01" min="0" placeholder="From" class="border rounded px-2 py-1 w-24" />
            <input name="price_to" type="number" step="0.01" min="0" placeholder="To" class="border rounded px-2 py-1 w-24" />
        </div>

        <div id="type-filters" class="flex items-center space-x-2"></div>
    </form>

    <div id="products-area" class="mt-6 bg-white shadow rounded">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">ID</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Manufacturer</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Price</th>
                    <th id="col-extra" class="px-4 py-2 text-left text-xs font-medium text-gray-500">Capacity (kWh)</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Description</th>
                </tr>
                </thead>
                <tbody>
                <!-- table body will be filled by AJAX -->
                <tr>
                    <td class="px-4 py-6" colspan="6">Loading products...</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="p-4 flex items-center justify-between">
                <div id="products-pagination" aria-label="Products pagination" data-total="0">
                    <div class="text-sm text-gray-600">Total products: 0</div>
                </div>
            <div id="products-status" class="text-sm text-gray-500"></div>
        </div>
    </div>
</div>

<script>
    (function(){
        const form = document.getElementById('products-filter');
        const tableBody = document.querySelector('table tbody');
        const pagination = document.getElementById('products-pagination');
        const status = document.getElementById('products-status');
        let controller = null;

        // initialize form values from URL so links are shareable
        (function initFormFromUrl() {
            try {
                const params = new URLSearchParams(window.location.search);
                for (const [k, v] of params.entries()) {
                    const el = form.querySelector('[name="' + k + '"]');
                    if (!el) continue;
                    if (el.tagName === 'SELECT' || el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                        el.value = v;
                    }
                }
            } catch (e) {
                // ignore
            }
        })();

        function qsFromForm(f) {
            return new URLSearchParams(new FormData(f)).toString();
        }

        function setStatus(text, isError = false) {
            status.textContent = text || '';
            status.className = isError ? 'text-sm text-red-600' : 'text-sm text-gray-500';
        }

        async function fetchAndRender(qs) {
            // abort previous
            if (controller) controller.abort();
            controller = new AbortController();
            const sig = controller.signal;

                const url = '{{ route('products.data') }}' + (qs ? ('?' + qs) : '');
            setStatus('Loading...');

            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' }, signal: sig });
                if (!res.ok) throw new Error('Network error');
                const json = await res.json();

                const type = json.type || 'batteries';
                let columns = [];
                if (type === 'connectors') columns = ['id','name','manufacturer','price','connector_type','description'];
                else if (type === 'solar_panels') columns = ['id','name','manufacturer','price','power_output','description'];
                else columns = ['id','name','manufacturer','price','capacity','description'];

                // update extra column header based on type
                const extraHeader = document.getElementById('col-extra');
                if (extraHeader) {
                    if (type === 'connectors') extraHeader.textContent = 'Connector Type';
                    else if (type === 'solar_panels') extraHeader.textContent = 'Power Output (W)';
                    else extraHeader.textContent = 'Capacity (kWh)';
                }

                // render rows
                tableBody.innerHTML = '';
                if ((json.items || []).length === 0) {
                    const tr = document.createElement('tr');
                    const td = document.createElement('td');
                    td.setAttribute('colspan', 6);
                    td.className = 'px-4 py-6';
                    td.textContent = 'No products found.';
                    tr.appendChild(td);
                    tableBody.appendChild(tr);
                } else {
                    for (const it of json.items) {
                        const tr = document.createElement('tr');
                        for (const col of columns) {
                            const td = document.createElement('td');
                            td.className = 'px-4 py-2 text-sm text-gray-700';
                            let val = it[col] ?? '';
                            if (col === 'price' && val !== null && val !== '') val = '$' + Number(val).toFixed(2);
                            else if (col === 'capacity' && val !== null && val !== '') val = val + ' kWh';
                            else if (col === 'power_output' && val !== null && val !== '') val = val + ' W';
                            else if (col === 'description' && val) val = val.length > 120 ? val.slice(0,120) + '…' : val;
                            td.textContent = val;
                            tr.appendChild(td);
                        }
                        tableBody.appendChild(tr);
                    }
                }

                // render pagination (preserves filters via form data)
                if (json.meta) {
                    renderPagination(json.meta);
                }

                // update manufacturers select safely
                if (json.manufacturers) {
                    const sel = form.querySelector('select[name="manufacturer"]');
                    if (sel) {
                        // clear and add default option
                        sel.innerHTML = '';
                        const opt0 = document.createElement('option');
                        opt0.value = '';
                        opt0.textContent = 'All manufacturers';
                        sel.appendChild(opt0);
                        for (const m of json.manufacturers) {
                            const o = document.createElement('option');
                            o.value = m;
                            o.textContent = m;
                            if (m === json.selectedManufacturer) o.selected = true;
                            sel.appendChild(o);
                        }
                    }
                }

                // render type-specific filters (compact inline groups)
                const typeFilters = document.getElementById('type-filters');
                if (typeFilters) {
                    typeFilters.innerHTML = '';
                    if (type === 'batteries') {
                        const wrap = document.createElement('div');
                        wrap.className = 'flex items-center space-x-2';
                        const label = document.createElement('span');
                        label.className = 'text-sm';
                        label.textContent = 'Capacity (kWh)';
                        const from = document.createElement('input');
                        from.name = 'capacity_from';
                        from.type = 'number';
                        from.step = '0.01';
                        from.min = '0';
                        from.placeholder = 'From';
                        from.className = 'border rounded px-2 py-1 w-24';
                        const to = document.createElement('input');
                        to.name = 'capacity_to';
                        to.type = 'number';
                        to.step = '0.01';
                        to.min = '0';
                        to.placeholder = 'To';
                        to.className = 'border rounded px-2 py-1 w-24';
                        wrap.appendChild(label);
                        wrap.appendChild(from);
                        wrap.appendChild(to);
                        typeFilters.appendChild(wrap);
                        from.addEventListener('input', debouncedFetch);
                        to.addEventListener('input', debouncedFetch);
                        // restore values from current QS if present
                        try {
                            const params = new URLSearchParams(qs || window.location.search.substring(1));
                            if (params.has('capacity_from')) from.value = params.get('capacity_from');
                            if (params.has('capacity_to')) to.value = params.get('capacity_to');
                        } catch (e) {}
                    } else if (type === 'solar_panels') {
                        const wrap = document.createElement('div');
                        wrap.className = 'flex items-center space-x-2';
                        const label = document.createElement('span');
                        label.className = 'text-sm';
                        label.textContent = 'Power (W)';
                        const from = document.createElement('input');
                        from.name = 'power_from';
                        from.type = 'number';
                        from.step = '1';
                        from.min = '0';
                        from.placeholder = 'From';
                        from.className = 'border rounded px-2 py-1 w-24';
                        const to = document.createElement('input');
                        to.name = 'power_to';
                        to.type = 'number';
                        to.step = '1';
                        to.min = '0';
                        to.placeholder = 'To';
                        to.className = 'border rounded px-2 py-1 w-24';
                        wrap.appendChild(label);
                        wrap.appendChild(from);
                        wrap.appendChild(to);
                        typeFilters.appendChild(wrap);
                        from.addEventListener('input', debouncedFetch);
                        to.addEventListener('input', debouncedFetch);
                        try {
                            const params = new URLSearchParams(qs || window.location.search.substring(1));
                            if (params.has('power_from')) from.value = params.get('power_from');
                            if (params.has('power_to')) to.value = params.get('power_to');
                        } catch (e) {}
                    } else if (type === 'connectors') {
                        const wrap = document.createElement('div');
                        wrap.className = 'flex items-center space-x-2';
                        const label = document.createElement('span');
                        label.className = 'text-sm';
                        label.textContent = 'Connector Type';
                        const sel = document.createElement('select');
                        sel.name = 'connector_type';
                        sel.className = 'border rounded px-2 py-1';
                        const opt0 = document.createElement('option');
                        opt0.value = '';
                        opt0.textContent = 'Any type';
                        sel.appendChild(opt0);
                        if (json.extra && Array.isArray(json.extra.connectorTypes)) {
                            for (const ct of json.extra.connectorTypes) {
                                const o = document.createElement('option');
                                o.value = ct;
                                o.textContent = ct;
                                sel.appendChild(o);
                            }
                        }
                        wrap.appendChild(label);
                        wrap.appendChild(sel);
                        typeFilters.appendChild(wrap);
                        sel.addEventListener('change', debouncedFetch);
                        try {
                            const params = new URLSearchParams(qs || window.location.search.substring(1));
                            if (params.has('connector_type')) sel.value = params.get('connector_type');
                        } catch (e) {}
                    }
                }

                // update URL to reflect the current filters (so the page is shareable)
                try {
                    const newUrl = window.location.pathname + (qs ? ('?' + qs) : '');
                    window.history.replaceState(null, '', newUrl);
                } catch (e) {}

                setStatus('');
            } catch (err) {
                if (err.name === 'AbortError') return; // ignore
                console.error(err);
                setStatus('Failed to load products', true);
            }
        }

        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            const qs = qsFromForm(form);
            fetchAndRender(qs);
        });

        // debounce helper to avoid rapid repeated fetches
        function debounce(fn, delay = 150) {
            let t = null;
            return function(...args) {
                if (t) clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        // Render pagination controls and preserve filters by using form values when building page requests
        function renderPagination(meta) {
            pagination.innerHTML = '';
            const info = document.createElement('div');
            info.className = 'text-sm text-gray-600 mr-4';
            info.textContent = `Total: ${meta.total}`;
            pagination.appendChild(info);

            const nav = document.createElement('div');
            nav.className = 'flex items-center space-x-1';

            function addBtn(label, page, disabled = false, isCurrent = false) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'px-2 py-1 border rounded text-sm ' + (isCurrent ? 'bg-gray-200' : '');
                btn.textContent = label;
                if (disabled) btn.disabled = true;
                if (!disabled && !isCurrent) {
                    btn.addEventListener('click', () => {
                        let pageInput = form.querySelector('input[name="page"]');
                        if (!pageInput) {
                            pageInput = document.createElement('input');
                            pageInput.type = 'hidden';
                            pageInput.name = 'page';
                            form.appendChild(pageInput);
                        }
                        pageInput.value = page;
                        fetchAndRender(qsFromForm(form));
                    });
                }
                nav.appendChild(btn);
            }

            const current = meta.current_page || 1;
            const last = meta.last_page || 1;

            addBtn('Prev', Math.max(1, current - 1), current <= 1);

            const start = Math.max(1, current - 3);
            const end = Math.min(last, start + 6);
            for (let p = start; p <= end; p++) {
                addBtn(p.toString(), p, false, p === current);
            }

            addBtn('Next', Math.min(last, current + 1), current >= last);

            pagination.appendChild(nav);
        }

        // validate price inputs before fetching
        function validatePriceInputs() {
            const fromInput = form.querySelector('input[name="price_from"]');
            const toInput = form.querySelector('input[name="price_to"]');
            const from = fromInput && fromInput.value !== '' ? parseFloat(fromInput.value) : null;
            const to = toInput && toInput.value !== '' ? parseFloat(toInput.value) : null;

            if (from !== null && Number.isNaN(from)) { setStatus('Invalid price from', true); return false; }
            if (to !== null && Number.isNaN(to)) { setStatus('Invalid price to', true); return false; }
            if (from !== null && from < 0) { setStatus('Price from must be >= 0', true); return false; }
            if (to !== null && to <= 0) { setStatus('Price to must be > 0', true); return false; }
            if (from !== null && to !== null && from > to) { setStatus('Price from must be less than or equal to Price to', true); return false; }
            setStatus('');
            return true;
        }

        const debouncedFetch = debounce(() => {
            if (!validatePriceInputs()) return;
            const qInput = form.querySelector('input[name="q"]');
            const qVal = qInput ? qInput.value.trim() : '';
            // require minimum 2 characters for non-empty search
            if (qVal.length > 0 && qVal.length < 2) return;
            // reset page to 1 when filters change
            const pageInput = form.querySelector('input[name="page"]');
            if (pageInput) pageInput.remove();
            fetchAndRender(qsFromForm(form));
        }, 150);

        // auto-submit selects: type and manufacturer
        const typeSel = form.querySelector('select[name="type"]');
        const manuSel = form.querySelector('select[name="manufacturer"]');
        if (typeSel) {
            typeSel.addEventListener('change', function () {
                // when type changes, clear manufacturer selection (manufacturers list will be updated by response)
                if (manuSel) manuSel.value = '';
                debouncedFetch();
            });
        }
        if (manuSel) {
            manuSel.addEventListener('change', debouncedFetch);
        }

        // also validate price inputs and auto-submit
        const priceFromInput = form.querySelector('input[name="price_from"]');
        const priceToInput = form.querySelector('input[name="price_to"]');
        if (priceFromInput) priceFromInput.addEventListener('input', debouncedFetch);
        if (priceToInput) priceToInput.addEventListener('input', debouncedFetch);

        // auto-submit search input with debounce
        const qInput = form.querySelector('input[name="q"]');
        if (qInput) qInput.addEventListener('input', debouncedFetch);

        // On page load, trigger AJAX to populate table (ensures consistent UI)
        fetchAndRender(qsFromForm(form));
    })();
</script>

</body>
</html>
