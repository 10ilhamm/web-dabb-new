/**
 * Profile Page Edit - JavaScript
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
                    if (!img.isExisting) {
                        _gambarStore.splice(idx, 1);
                        delete _files[img.id];
                    } else {
                        _gambarStore.splice(idx, 1);
                        const removeInput = document.getElementById(
                            "removed_images_input",
                        );
                        if (removeInput) {
                            const current = removeInput.value
                                ? JSON.parse(removeInput.value)
                                : [];
                            current.push(img.path);
                            removeInput.value = JSON.stringify(current);
                        }
                    }
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

        _chartInstances.forEach(function (c) {
            c.destroy();
        });
        _chartInstances = [];

        if (!data || Object.keys(data).length === 0) {
            container.innerHTML =
                '<p class="text-xs text-gray-400 text-center py-8">Tidak ada data untuk ditampilkan. Pilih field data dan tipe grafik, lalu klik "Generate Grafik"</p>';
            return;
        }

        let html = '<div class="chart-preview-container">';

        Object.keys(data).forEach(function (key) {
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

            html +=
                '<div class="chart-card">' +
                '<p class="chart-card-title">' +
                (chart.title || key) +
                chartTypeLabel +
                "</p>" +
                '<div style="height:' +
                (isPie ? "250px" : "200px") +
                ';position:relative">' +
                '<canvas id="' +
                chartId +
                '"></canvas>' +
                "</div></div>";
        });

        html += "</div>";
        container.innerHTML = html;

        // Render each chart
        Object.keys(data).forEach(function (key) {
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
        fields.forEach(function (field) {
            const label = _availableFields[field] || field;

            html +=
                '<div class="chart-config-item" data-field="' +
                field +
                '">' +
                '<div class="chart-config-header">' +
                '<span class="chart-config-label">' +
                label +
                "</span>" +
                '<button type="button" onclick="removeFieldFromConfig(\'' +
                field +
                '\')" class="chart-config-remove">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>' +
                "</svg>" +
                "</button>" +
                "</div>" +
                '<div class="chart-config-types">' +
                '<button type="button" onclick="toggleChartTypeForField(\'' +
                field +
                "', 'pie')\" class=\"chart-type-btn " +
                (isChartTypeSelectedForField(field, "pie") ? "active" : "") +
                '">' +
                "Pie Chart" +
                "</button>" +
                '<button type="button" onclick="toggleChartTypeForField(\'' +
                field +
                "', 'bar')\" class=\"chart-type-btn " +
                (isChartTypeSelectedForField(field, "bar") ? "active" : "") +
                '">' +
                "Bar Chart" +
                "</button>" +
                "</div>" +
                "</div>";
        });

        container.innerHTML = html;
    }

    // Profile Page Form Alpine Component
    function profilePageForm() {
        return {
            pageType: window.pageType || "tugas_fungsi",
            title: window.pageTitle || "",
            linkText: window.pageLinkText || "",
            linkUrl: window.pageLinkUrl || "",
            subtitle: window.pageSubtitle || "",
            isGeneratingChart: false,
            selectedField: "",
            availableFields: {},
            sectionModal: {
                open: false,
                mode: "add",
                id: null,
                title: "",
                description: "",
                order: 0,
            },
            editSectionModal: {
                open: false,
                id: null,
                title: "",
                description: "",
                order: 0,
            },
            deleteSectionModal: { open: false, id: null, name: "" },

            async init() {
                // Initialize existing images from window.existingImages
                if (window.existingImages && window.existingImages.length) {
                    window.existingImages.forEach(function (img, idx) {
                        _gambarStore.push({
                            id: "existing-" + idx,
                            preview: img.url,
                            path: img.path,
                            x: img.x || 50,
                            y: img.y || 50,
                            isExisting: true,
                        });
                    });
                }
                renderGambarPreviews();

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

                // Initialize chart types from existing data
                const chartDataInput =
                    document.getElementById("chart_data_input");
                if (chartDataInput && chartDataInput.value) {
                    try {
                        const chartData = JSON.parse(chartDataInput.value);
                        // Only restore config for edit UI, don't render charts automatically
                        // Charts should only render when user clicks "Generate Grafik"
                        Object.keys(chartData).forEach(function (key) {
                            const chart = chartData[key];
                            if (chart.field) {
                                const type =
                                    chart.type === "pie" ? "pie" : "bar";
                                if (!_chartConfig[chart.field]) {
                                    _chartConfig[chart.field] = [];
                                }
                                if (!_chartConfig[chart.field].includes(type)) {
                                    _chartConfig[chart.field].push(type);
                                }
                            }
                        });
                        renderChartConfigList();
                        // Don't call renderChartPreview here - will be called when Generate Grafik is clicked
                    } catch (e) {
                        console.error("Error parsing chart data:", e);
                    }
                }
            },

            onTypeChange() {},

            async handleGambarChange(event) {
                const files = Array.from(event.target.files);
                const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
                const oversizedFiles = [];

                // Validate file sizes first before compression
                for (let file of files) {
                    if (file.size > MAX_FILE_SIZE) {
                        oversizedFiles.push(
                            file.name +
                                " (" +
                                (file.size / 1024 / 1024).toFixed(2) +
                                "MB)",
                        );
                    }
                }

                if (oversizedFiles.length > 0) {
                    alert(
                        "File berikut terlalu besar (maksimal 10MB per file):\n\n" +
                            oversizedFiles.join("\n"),
                    );
                    event.target.value = "";
                    return;
                }

                const compressionPromises = files.map(async function (file) {
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
                const config = {};
                Object.keys(_chartConfig).forEach(function (field) {
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

            // Section management
            openAddSection() {
                const sectionsCount = window.sectionsCount || 0;
                this.sectionModal = {
                    open: true,
                    mode: "add",
                    id: null,
                    title: "",
                    description: "",
                    order: sectionsCount + 1,
                };
            },

            openEditSection(section) {
                this.editSectionModal = {
                    open: true,
                    id: section.id,
                    title: section.title,
                    description: section.description || "",
                    order: section.order,
                };
            },

            openDeleteSection(id, name) {
                this.deleteSectionModal = { open: true, id, name };
            },

            submitDeleteSection() {
                const form = document.createElement("form");
                form.method = "POST";
                form.action = window.sectionDeleteUrl.replace(
                    "__SECTION_ID__",
                    this.deleteSectionModal.id,
                );
                form.innerHTML =
                    '<input type="hidden" name="_token" value="' +
                    window.csrfToken +
                    '"><input type="hidden" name="_method" value="DELETE">';
                document.body.appendChild(form);
                form.submit();
            },

            submitSectionForm(_event, mode) {
                // For add mode, submit the form normally
                if (mode === "add") {
                    // Let the form submit naturally
                    return true;
                }
                // For edit mode, close modal after submit
                this.editSectionModal.open = false;
                return true;
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

    window.removeExistingLogo = function () {
        const removeInput = document.createElement("input");
        removeInput.type = "hidden";
        removeInput.name = "remove_logo";
        removeInput.value = "1";
        document.getElementById("pageForm").appendChild(removeInput);
        document.getElementById("logo_preview").classList.add("hidden");
        document.getElementById("logo_uploaded")?.classList.add("hidden");
        document.getElementById("logo-upload-area").classList.remove("hidden");
    };

    // RTE content setter — works reliably across RTE versions
    function setRTEContent(rte, html) {
        if (!rte) return;
        try {
            if (typeof rte.setHTMLCode === "function") {
                rte.setHTMLCode(html);
            } else if (typeof rte.setHTML === "function") {
                rte.setHTML(html);
            } else if (typeof rte.setValue === "function") {
                rte.setValue(html);
            }
        } catch (e) {
            console.warn("[RTE] setRTEContent failed:", e);
        }
    }

    // Form submit handler
    window.initProfileEditForm = function () {
        console.log("[RTE] initProfileEditForm called");

        // Grab existing content BEFORE clearing
        var container = document.getElementById("div_editor1");
        var initialDescriptionHtml = container ? container.innerHTML || "" : "";

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
            if (!document.getElementById("div_editor1")) {
                rteRetries++;
                if (rteRetries < 100) {
                    setTimeout(initRTE, 100);
                }
                return;
            }

            // Clear container so RTE can initialize in a clean DOM
            var editorContainer = document.getElementById("div_editor1");
            editorContainer.innerHTML = "";
            console.log(
                "[RTE] Creating RichTextEditor with initial content length:",
                initialDescriptionHtml.length,
            );

            try {
                editor1 = new RichTextEditor("#div_editor1", {
                    base_url: "/richtexteditor",
                    file_upload_handler: function (file, callback) {
                        var formData = new FormData();
                        formData.append("file", file);
                        formData.append("_token", window.csrfToken || "");
                        fetch(
                            window.rteUploadUrl || "/cms/settings/rte-upload",
                            { method: "POST", body: formData },
                        )
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

                // Set content after editor is ready — multiple attempts for iframe readiness
                setRTEContent(editor1, initialDescriptionHtml);
                setTimeout(function () {
                    setRTEContent(editor1, initialDescriptionHtml);
                }, 300);
                setTimeout(function () {
                    setRTEContent(editor1, initialDescriptionHtml);
                }, 800);
                setTimeout(function () {
                    // Final check: if editor is still empty but we have content, force-set once more
                    try {
                        var currentContent = "";
                        if (typeof editor1.getHTMLCode === "function")
                            currentContent = editor1.getHTMLCode();
                        else if (typeof editor1.getHTML === "function")
                            currentContent = editor1.getHTML();
                        if (
                            (!currentContent ||
                                currentContent.trim() === "" ||
                                currentContent.trim() === "<p><br></p>" ||
                                currentContent.trim() === "<br>") &&
                            initialDescriptionHtml &&
                            initialDescriptionHtml.trim() !== ""
                        ) {
                            setRTEContent(editor1, initialDescriptionHtml);
                        }
                    } catch (e) {}
                }, 1500);

                console.log("[RTE] RichTextEditor created successfully");
            } catch (e) {
                console.error("[RTE] Error creating RichTextEditor:", e);
            }
        }
        // Wait for scripts to fully load, then init
        setTimeout(initRTE, 500);

        // Initialize chart config list
        renderChartConfigList();

        // Initialize existing_images_input on page load so existing images are sent to controller
        var existingImagesInitInput = document.getElementById(
            "existing_images_container",
        );
        if (
            existingImagesInitInput &&
            window.existingImages &&
            window.existingImages.length
        ) {
            // Clear previous inputs
            existingImagesInitInput.innerHTML = "";
            // Create hidden input for each existing image
            window.existingImages.forEach(function (img, idx) {
                var input = document.createElement("input");
                input.type = "hidden";
                input.name = "existing_images[]";
                input.value = img.path;
                existingImagesInitInput.appendChild(input);
            });
        }

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
                    .filter(function (i) {
                        return !i.isExisting;
                    })
                    .map(function (i) {
                        return _files[i.id];
                    })
                    .filter(Boolean);
                if (gambarFiles.length) {
                    const dt = new DataTransfer();
                    gambarFiles.forEach(function (f) {
                        dt.items.add(f);
                    });
                    const fileInput = document.createElement("input");
                    fileInput.type = "file";
                    fileInput.name = "images[]";
                    fileInput.multiple = true;
                    fileInput.files = dt.files;
                    fileInput.className = "hidden";
                    form.appendChild(fileInput);
                }

                // Update existing_images container
                const existingImagesContainer = document.getElementById(
                    "existing_images_container",
                );
                if (existingImagesContainer) {
                    existingImagesContainer.innerHTML = "";
                    _gambarStore
                        .filter(function (i) {
                            return i.isExisting;
                        })
                        .forEach(function (img) {
                            var input = document.createElement("input");
                            input.type = "hidden";
                            input.name = "existing_images[]";
                            input.value = img.path;
                            existingImagesContainer.appendChild(input);
                        });
                }

                // Image positions
                _gambarStore.forEach(function (img) {
                    const posInput = document.createElement("input");
                    posInput.type = "hidden";
                    posInput.name = "image_positions[]";
                    posInput.value = img.x + "% " + img.y + "%";
                    form.appendChild(posInput);
                });

                // Disable file input default
                form.querySelectorAll(
                    'input[type="file"][name="gambar_files"]',
                ).forEach(function (i) {
                    i.disabled = true;
                });

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
