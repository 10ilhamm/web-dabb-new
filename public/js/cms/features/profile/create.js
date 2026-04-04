/**
 * Profile Page Create - JavaScript
 */
(function () {
    let _idCounter = 1;
    const _files = {};
    const _gambarStore = [];
    let _chartInstances = [];
    let editor1 = null;
    let _availableFields = {};
    let _chartConfig = {}; // { field: ['pie', 'bar'] or [] }

    function getNextId() {
        return "gbr-" + Date.now() + "-" + _idCounter++;
    }

    function compressImage(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement("canvas");
                    let width = img.width,
                        height = img.height;
                    const MAX_SIZE = 1280;
                    if (width > height && width > MAX_SIZE) {
                        height *= MAX_SIZE / width;
                        width = MAX_SIZE;
                    } else if (height > MAX_SIZE) {
                        width *= MAX_SIZE / height;
                        height = MAX_SIZE;
                    }
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext("2d");
                    ctx.fillStyle = "#FFFFFF";
                    ctx.fillRect(0, 0, width, height);
                    ctx.drawImage(img, 0, 0, width, height);
                    const quality = file.type === "image/png" ? undefined : 0.9;
                    const ext = file.type === "image/png" ? "png" : "jpg";
                    const newName =
                        file.name.replace(/\.[^/.]+$/, "") + "." + ext;
                    canvas.toBlob(
                        (blob) => {
                            resolve({
                                file: new File([blob], newName, {
                                    type: file.type,
                                    lastModified: Date.now(),
                                }),
                                preview: e.target.result,
                            });
                        },
                        file.type,
                        quality,
                    );
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    function renderGambarPreviews() {
        const container = document.getElementById("gambar-previews");
        if (!container) return;
        container.innerHTML = "";

        if (!_gambarStore.length) return;

        const cols = Math.min(Math.max(_gambarStore.length, 1), 4);
        const grid = document.createElement("div");
        grid.className = "grid gap-3 mb-3";
        grid.style.gridTemplateColumns = "repeat(" + cols + ", 1fr)";

        _gambarStore.forEach((img) => {
            const wrapper = document.createElement("div");
            wrapper.className = "relative group";
            wrapper.dataset.imgId = img.id;

            const dragBox = document.createElement("div");
            dragBox.className =
                "relative overflow-hidden rounded-lg bg-gray-900 cursor-crosshair";
            dragBox.style.aspectRatio = "4/3";

            const imgEl = document.createElement("img");
            imgEl.src = img.preview;
            imgEl.className =
                "absolute w-full h-full object-cover transition-transform duration-300";
            imgEl.style.objectPosition = img.x + "% " + img.y + "%";
            dragBox.appendChild(imgEl);

            const focal = document.createElement("div");
            focal.className =
                "absolute w-5 h-5 border-2 border-white rounded-full shadow-lg pointer-events-none flex items-center justify-center";
            focal.style.cssText =
                "background-color:rgba(59,130,246,0.6);transform:translate(-50%,-50%);left:" +
                img.x +
                "%;top:" +
                img.y +
                "%;";
            const dot = document.createElement("div");
            dot.className = "w-1 h-1 bg-white rounded-full";
            focal.appendChild(dot);
            dragBox.appendChild(focal);

            dragBox.addEventListener("mousedown", function (e) {
                if (e.target.closest("button")) return;
                e.preventDefault();
                const update = (ev) => {
                    const rect = dragBox.getBoundingClientRect();
                    if (rect.width === 0) return;
                    const px = parseFloat(
                        Math.max(
                            0,
                            Math.min(
                                100,
                                ((ev.clientX - rect.left) / rect.width) * 100,
                            ),
                        ).toFixed(2),
                    );
                    const py = parseFloat(
                        Math.max(
                            0,
                            Math.min(
                                100,
                                ((ev.clientY - rect.top) / rect.height) * 100,
                            ),
                        ).toFixed(2),
                    );
                    imgEl.style.objectPosition = px + "% " + py + "%";
                    focal.style.left = px + "%";
                    focal.style.top = py + "%";
                    img.x = px;
                    img.y = py;
                };
                const stop = () => {
                    window.removeEventListener("mousemove", update);
                    window.removeEventListener("mouseup", stop);
                };
                window.addEventListener("mousemove", update);
                window.addEventListener("mouseup", stop);
                update(e);
            });

            wrapper.appendChild(dragBox);

            const delBtn = document.createElement("button");
            delBtn.type = "button";
            delBtn.className =
                "absolute bg-red-500 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-600 transition-colors cursor-pointer z-50";
            delBtn.style.cssText =
                "width:22px;height:22px;top:-6px;right:-6px;line-height:1";
            delBtn.innerHTML =
                '<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
            delBtn.addEventListener("click", (e) => {
                e.stopPropagation();
                const idx = _gambarStore.findIndex((i) => i.id === img.id);
                if (idx !== -1) {
                    _gambarStore.splice(idx, 1);
                    delete _files[img.id];
                }
                renderGambarPreviews();
            });
            wrapper.appendChild(delBtn);
            grid.appendChild(wrapper);
        });

        container.appendChild(grid);
    }

    // Chart rendering functions
    const chartColors = [
        "#3B82F6",
        "#06B6D4",
        "#10B981",
        "#F59E0B",
        "#EF4444",
        "#8B5CF6",
        "#EC4899",
        "#14B8A6",
        "#F97316",
        "#6366F1",
    ];

    function renderChartPreview(data) {
        const container = document.getElementById("chart_preview");
        if (!container) return;

        _chartInstances.forEach((c) => c.destroy());
        _chartInstances = [];

        if (!data || Object.keys(data).length === 0) {
            container.innerHTML =
                '<p class="text-xs text-gray-400 text-center py-8">Tidak ada data untuk ditampilkan. Pilih field data dan tipe grafik, lalu klik "Generate Grafik"</p>';
            return;
        }

        let html = '<div class="chart-preview-container">';

        Object.keys(data).forEach((key) => {
            const chart = data[key];
            // Skip if labels or data is missing/invalid
            if (
                !chart.labels ||
                !chart.data ||
                !Array.isArray(chart.labels) ||
                !Array.isArray(chart.data)
            ) {
                console.warn("Invalid chart data for key:", key, chart);
                return;
            }
            const chartId = "chart-" + key;
            const isPie = chart.type === "pie";
            const chartTypeLabel = chart.type
                ? isPie
                    ? " (Pie)"
                    : " (Bar)"
                : "";

            html += `
                <div class="chart-card">
                    <p class="chart-card-title">${chart.title || key}${chartTypeLabel}</p>
                    <div style="height:${isPie ? "250px" : "200px"};position:relative">
                        <canvas id="${chartId}"></canvas>
                    </div>
                </div>`;
        });

        html += "</div>";
        container.innerHTML = html;

        // Render each chart
        Object.keys(data).forEach((key) => {
            const chart = data[key];
            // Skip if labels or data is missing/invalid
            if (
                !chart.labels ||
                !chart.data ||
                !Array.isArray(chart.labels) ||
                !Array.isArray(chart.data)
            ) {
                return;
            }
            const chartId = "chart-" + key;
            const canvasEl = document.getElementById(chartId);
            if (!canvasEl) return;

            if (chart.type === "pie") {
                _chartInstances.push(
                    new Chart(canvasEl.getContext("2d"), {
                        type: "pie",
                        data: {
                            labels: chart.labels,
                            datasets: [
                                {
                                    data: chart.data,
                                    backgroundColor:
                                        chart.colors ||
                                        chartColors.slice(
                                            0,
                                            chart.labels.length,
                                        ),
                                    borderWidth: 2,
                                    borderColor: "#fff",
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: "bottom",
                                    labels: { font: { size: 11 }, padding: 8 },
                                },
                            },
                        },
                    }),
                );
            } else {
                _chartInstances.push(
                    new Chart(canvasEl.getContext("2d"), {
                        type: "bar",
                        data: {
                            labels: chart.labels,
                            datasets: [
                                {
                                    label: "Jumlah",
                                    data: chart.data,
                                    backgroundColor:
                                        chart.colors ||
                                        chartColors.slice(
                                            0,
                                            chart.labels.length,
                                        ),
                                    borderRadius: 4,
                                    borderSkipped: false,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1, font: { size: 10 } },
                                },
                                x: { ticks: { font: { size: 9 } } },
                            },
                        },
                    }),
                );
            }
        });
    }

    // Add a new field to chart config
    function addFieldToConfig(field, types) {
        if (!field || _chartConfig[field]) return;
        _chartConfig[field] = types;
        renderChartConfigList();
    }

    // Remove a field from chart config
    function removeFieldFromConfig(field) {
        delete _chartConfig[field];
        renderChartConfigList();
    }

    // Update chart types for a field
    function updateFieldChartTypes(field, types) {
        if (!_chartConfig[field]) return;
        _chartConfig[field] = types;
    }

    // Toggle chart type for a field
    function toggleChartTypeForField(field, type) {
        if (!_chartConfig[field]) return;
        const idx = _chartConfig[field].indexOf(type);
        if (idx > -1) {
            _chartConfig[field].splice(idx, 1);
        } else {
            _chartConfig[field].push(type);
        }
        // Must have at least one type
        if (_chartConfig[field].length === 0) {
            _chartConfig[field].push(type);
        }
        // Re-render to update UI
        renderChartConfigList();
    }

    // Check if a chart type is selected for a field
    function isChartTypeSelectedForField(field, type) {
        return _chartConfig[field] && _chartConfig[field].includes(type);
    }

    // Render the chart config list
    function renderChartConfigList() {
        const container = document.getElementById("chart-config-list");
        if (!container) return;

        const fields = Object.keys(_chartConfig);
        if (fields.length === 0) {
            container.innerHTML =
                '<p class="text-xs text-gray-400 py-2">Pilih field data di atas untuk menambahkan grafik</p>';
            return;
        }

        let html = "";
        fields.forEach((field) => {
            const label = _availableFields[field] || field;
            const types = _chartConfig[field] || [];
            const isTanggalLahir = field === "tanggal_lahir";

            html += `
                <div class="chart-config-item" data-field="${field}">
                    <div class="chart-config-header">
                        <span class="chart-config-label">${label}</span>
                        <button type="button" onclick="removeFieldFromConfig('${field}')" class="chart-config-remove">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="chart-config-types">
                        <button type="button"
                            onclick="toggleChartTypeForField('${field}', 'pie')"
                            class="chart-type-btn ${isChartTypeSelectedForField(field, "pie") ? "active" : ""}"
                            ${isTanggalLahir ? 'title="Usia dihitung dari tanggal lahir"' : ""}>
                            Pie Chart
                        </button>
                        <button type="button"
                            onclick="toggleChartTypeForField('${field}', 'bar')"
                            class="chart-type-btn ${isChartTypeSelectedForField(field, "bar") ? "active" : ""}">
                            Bar Chart
                        </button>
                    </div>
                </div>`;
        });

        container.innerHTML = html;
    }

    // Profile Page Form Alpine Component
    function profilePageForm() {
        return {
            pageType: "tugas_fungsi",
            title: "",
            linkText: "",
            linkUrl: "",
            subtitle: "",
            isGeneratingChart: false,
            selectedField: "",
            availableFields: {},

            async init() {
                // Fetch available data fields
                try {
                    const response = await fetch(window.dataFieldsUrl);
                    const data = await response.json();
                    this.availableFields = data;
                    _availableFields = data;
                    window.dispatchEvent(
                        new CustomEvent("fields-loaded", { detail: data }),
                    );
                } catch (e) {
                    console.error("Failed to load data fields:", e);
                }
            },

            onTypeChange() {
                // This is called when the page type changes
            },

            async handleGambarChange(event) {
                const files = Array.from(event.target.files);
                const compressionPromises = files.map(async (file) => {
                    const compressed = await compressImage(file);
                    const id = getNextId();
                    _files[id] = compressed.file;
                    _gambarStore.push({
                        id,
                        preview: compressed.preview,
                        x: 50,
                        y: 50,
                        isExisting: false,
                    });
                });
                // Wait for all images to be compressed before rendering
                await Promise.all(compressionPromises);
                renderGambarPreviews();
                event.target.value = "";
            },

            addField() {
                const field = this.selectedField;
                if (!field || _chartConfig[field]) return;
                addFieldToConfig(field, ["pie", "bar"]);
                this.selectedField = "";
            },

            async generateChart() {
                // Build config from _chartConfig
                const config = {};
                Object.keys(_chartConfig).forEach((field) => {
                    if (_chartConfig[field].length > 0) {
                        config[field] = _chartConfig[field];
                    }
                });

                if (Object.keys(config).length === 0) {
                    alert("Pilih minimal satu field data dan tipe grafik");
                    return;
                }

                this.isGeneratingChart = true;
                try {
                    const chartUrl =
                        window.chartGenerateUrl ||
                        "/cms/features/" +
                            window.featureId +
                            "/generate-profile-chart";
                    const url =
                        chartUrl +
                        "?config=" +
                        encodeURIComponent(JSON.stringify(config));
                    const response = await fetch(url);
                    const data = await response.json();
                    document.getElementById("chart_data_input").value =
                        JSON.stringify(data);
                    renderChartPreview(data);
                } catch (e) {
                    alert("Gagal generate grafik: " + e.message);
                } finally {
                    this.isGeneratingChart = false;
                }
            },
        };
    }

    // Make functions globally available
    window.addFieldToConfig = addFieldToConfig;
    window.removeFieldFromConfig = removeFieldFromConfig;
    window.updateFieldChartTypes = updateFieldChartTypes;
    window.toggleChartTypeForField = toggleChartTypeForField;
    window.isChartTypeSelectedForField = isChartTypeSelectedForField;
    window.renderChartConfigList = renderChartConfigList;
    window.renderChartPreview = renderChartPreview;

    // Logo preview functions (global)
    window.previewLogo = function (input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.getElementById("logo_preview");
                const img = document.getElementById("logo_preview_img");
                img.src = e.target.result;
                preview.classList.remove("hidden");
                document
                    .getElementById("logo-upload-area")
                    .classList.add("hidden");
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    window.removeLogo = function () {
        document.getElementById("logo_input").value = "";
        document.getElementById("logo_preview").classList.add("hidden");
        document.getElementById("logo_preview_img").src = "";
        document.getElementById("logo-upload-area").classList.remove("hidden");
    };

    // Form submit handler
    window.initProfileCreateForm = function () {
        console.log("[RTE] initProfileCreateForm called");
        // RTE init - wait for CDN and DOM to be ready
        var rteRetries = 0;
        function initRTE() {
            console.log(
                "[RTE] initRTE attempt",
                rteRetries,
                "RichTextEditor available:",
                typeof RichTextEditor !== "undefined",
            );
            if (typeof RichTextEditor === "undefined") {
                rteRetries++;
                if (rteRetries < 100) {
                    setTimeout(initRTE, 100);
                }
                return;
            }
            var container = document.getElementById("div_editor1");
            console.log("[RTE] container found:", !!container);
            if (!container) {
                rteRetries++;
                if (rteRetries < 100) {
                    setTimeout(initRTE, 100);
                }
                return;
            }

            console.log("[RTE] Creating RichTextEditor...");
            var rteUploadUrl =
                window.rteUploadUrl || "/cms/settings/rte-upload";
            try {
                // Define base_url - local path
                var rteBaseUrl = "/richtexteditor";

                editor1 = new RichTextEditor("#div_editor1", {
                    base_url: rteBaseUrl,
                    file_upload_handler: function (file, callback) {
                        var formData = new FormData();
                        formData.append("file", file);
                        formData.append("_token", window.csrfToken || "");
                        fetch(rteUploadUrl, { method: "POST", body: formData })
                            .then(function (r) {
                                return r.json();
                            })
                            .then(function (result) {
                                callback(result.url);
                            })
                            .catch(function (err) {
                                console.error(err);
                                alert("Upload gagal.");
                            });
                    },
                });
                console.log(
                    "[RTE] RichTextEditor created successfully with base_url:",
                    rteBaseUrl,
                );
            } catch (e) {
                console.error("[RTE] Error creating RichTextEditor:", e);
            }
        }
        // Wait for scripts to fully load, then init
        setTimeout(initRTE, 500);

        // Initialize chart config list
        renderChartConfigList();

        // Form submit
        document
            .getElementById("pageForm")
            .addEventListener("submit", function (e) {
                if (typeof editor1 !== "undefined" && editor1) {
                    var html = editor1.getHTMLCode();
                    document.getElementById("description_input").value = html;
                }

                // Handle gambar files
                const form = e.target;
                const gambarFiles = _gambarStore
                    .filter((i) => !i.isExisting)
                    .map((i) => _files[i.id])
                    .filter(Boolean);
                if (gambarFiles.length) {
                    const dt = new DataTransfer();
                    gambarFiles.forEach((f) => dt.items.add(f));
                    const fileInput = document.createElement("input");
                    fileInput.type = "file";
                    fileInput.name = "images[]";
                    fileInput.multiple = true;
                    fileInput.files = dt.files;
                    fileInput.className = "hidden";
                    form.appendChild(fileInput);
                }

                // Image positions
                _gambarStore.forEach((img) => {
                    const posInput = document.createElement("input");
                    posInput.type = "hidden";
                    posInput.name = "image_positions[]";
                    posInput.value = img.x + "% " + img.y + "%";
                    form.appendChild(posInput);
                });

                // Disable file input default
                form.querySelectorAll('input[type="file"]').forEach(
                    (i) => (i.disabled = true),
                );

                const btn = document.getElementById("submitBtn");
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = "Menyimpan...";
                }
            });
    };

    // Export for Alpine
    window.profilePageForm = profilePageForm;
})();
