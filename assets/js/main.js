        // Function to handle property type selection
        function handlePropertyTypeChange(selectElement) {
            // This function is no longer needed as we removed the "Άλλο" option
        }

        // Add event listeners to existing property type selects
        document.addEventListener('DOMContentLoaded', function() {
            // This event listener is no longer needed as we removed the "Άλλο" option
        });

        // Modify addPropertyEntry function to include the event listener
        function addPropertyEntry() {
            const container = document.getElementById('properties-container');
            const index = container.children.length;
            const currentYear = new Date().getFullYear();
            let yearOptions = '<option value="">Επιλέξτε Χρόνο</option>';
            for (let year = currentYear; year >= 1900; year--) {
                yearOptions += `<option value="${year}">${year}</option>`;
            }
            
            const template = `
                <div class="property-entry entry-container border rounded p-3 mb-3 position-relative">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Είδος</label>
                            <select name="properties[${index}][type]" class="form-select property-type">
                                <option value="">Επιλέξτε</option>
                                <option value="Σπίτι">Σπίτι</option>
                                <option value="Διαμέρισμα">Διαμέρισμα</option>
                                <option value="Οικόπεδο">Οικόπεδο</option>
                                <option value="Χωράφι">Χωράφι</option>
                                <option value="Κατάστημα">Κατάστημα</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Τοποθεσία</label>
                            <textarea name="properties[${index}][location]" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Εκταση (m²)</label>
                            <input type="text" name="properties[${index}][area]" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Τοπογραφικά Στοιχεία</label>
                            <input type="text" name="properties[${index}][topographic_data]" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Εμπράγματα δικαιώματα και βάρη επ' αυτής</label>
                            <textarea name="properties[${index}][rights_burdens]" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Τρόπος απόκτησης</label>
                            <input type="text" name="properties[${index}][acquisition_mode]" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Χρόνος απόκτησης</label>
                            <select name="properties[${index}][acquisition_date]" class="form-select">
                                ${yearOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Αξία απόκτησης (€)</label>
                            <input type="text" name="properties[${index}][acquisition_value]" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Τρέχουσα αξία (€)</label>
                            <input type="text" name="properties[${index}][current_value]" class="form-control">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new vehicle entry
        function addVehicleEntry() {
            const container = document.getElementById('vehicles-container');
            const index = container.children.length;
            const currentYear = new Date().getFullYear();
            let yearOptions = '<option value="">Επιλέξτε Χρόνο</option>';
            for (let year = currentYear; year >= 1900; year--) {
                yearOptions += `<option value="${year}">${year}</option>`;
            }
            
            const template = `
                <div class="vehicle-entry entry-container border rounded p-3 mb-3 position-relative">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Τύπος</label>
                            <select name="vehicles[${index}][type]" class="form-select">
                                <option value="">Επιλέξτε Τύπο</option>
                                <option value="Αυτοκίνητο">Αυτοκίνητο</option>
                                <option value="Μοτοσικλέτα">Μοτοσικλέτα</option>
                                <option value="Σκάφος">Σκάφος</option>
                                <option value="Άλλο">Άλλο</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Μάρκα</label>
                            <input type="text" name="vehicles[${index}][brand]" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Χρονολογία παραγωγής</label>
                            <select name="vehicles[${index}][manu_year]" class="form-select">
                                ${yearOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Αξία (€)</label>
                            <input type="text" name="vehicles[${index}][value]" class="form-control">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new liquid asset entry
        function addLiquidAssetEntry() {
            const container = document.getElementById('liquid-assets-container');
            const index = container.children.length;
            const template = `
                <div class="liquid-asset-entry entry-container border rounded p-3 mb-3 position-relative">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Είδος Κινητής Αξίας</label>
                            <select name="liquid_assets[${index}][type]" class="form-select">
                                <option value="">Επιλέξτε Είδος</option>
                                <option value="Χρεόγραφα">Χρεόγραφα</option>
                                <option value="Χρεωστικά Ομόλογα">Χρεωστικά Ομόλογα</option>
                                <option value="Ομολογίες">Ομολογίες</option>
                                <option value="Τίτλοι">Τίτλοι</option>
                                <option value="Μετοχές">Μετοχές</option>
                                <option value="Μερίσματα">Μερίσματα</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Περιγραφή</label>
                            <textarea name="liquid_assets[${index}][description]" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Αριθμός σε Κατοχή</label>
                            <input type="text" name="liquid_assets[${index}][amount]" class="form-control" >
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new deposit entry
        function addDepositEntry() {
            const container = document.getElementById('deposits-container');
            const index = container.children.length;
            const template = `
                <div class="deposit-entry entry-container border rounded p-3 mb-3 position-relative">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Όνομα Τράπεζας</label>
                            <input type="text" name="deposits[${index}][bank_name]" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ποσό Κατάθεσης (€)</label>
                            <input type="text" name="deposits[${index}][amount]" class="form-control">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new insurance entry
        function addInsuranceEntry() {
            const container = document.getElementById('insurance-container');
            const index = container.children.length;
            const template = `
                <div class="insurance-entry entry-container border rounded p-3 mb-3 position-relative">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Όνομα Ασφαλιστικής Εταιρείας</label>
                            <input type="text" name="insurance[${index}][insurance_name]" class="form-control" >
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Αριθμός Συμβολαίου</label>
                            <input type="text" name="insurance[${index}][contract_num]" class="form-control" >
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Εισοδήματα (€)</label>
                            <input type="text" name="insurance[${index}][earnings]" class="form-control">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new debt entry
        function addDebtEntry() {
            const container = document.getElementById('debts-container');
            const index = container.children.length;
            const template = `
                <div class="debt-entry entry-container border rounded p-3 mb-3 position-relative">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Όνομα Πιστωτή</label>
                            <input type="text" name="debts[${index}][creditor_name]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Είδος Χρέους</label>
                            <input type="text" name="debts[${index}][type]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ποσό Χρέους (€)</label>
                            <input type="text" name="debts[${index}][amount]" class="form-control">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new business entry
        function addBusinessEntry() {
            const container = document.getElementById('business-container');
            const index = container.children.length;
            const template = `
                <div class="business-entry entry-container border rounded p-3 mb-3 position-relative">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Όνομα Επιχειρήσης</label>
                            <input type="text" name="business[${index}][business_name]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Είδος Επιχειρήσης</label>
                            <input type="text" name="business[${index}][business_type]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Είδος Συμμετοχής</label>
                            <input type="text" name="business[${index}][participation_type]" class="form-control">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new other income entry
        function addOtherIncomeEntry() {
            const container = document.getElementById('other-incomes-container');
            const index = container.children.length;
            const template = `
                <div class="other-income-entry entry-container border rounded p-3 mb-3 position-relative">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Τύπος Εισοδήματος</label>
                            <select name="other_incomes[${index}][type]" class="form-select">
                                <option value="">Επιλέξτε Τύπο</option>
                                <option value="Ενοίκια">Ενοίκια</option>
                                <option value="Τόκοι / Καταθέσεις">Τόκοι / Καταθέσεις</option>
                                <option value="Μερίσματα">Μερίσματα</option>
                                <option value="Κέρδη από επενδύσεις">Κέρδη από επενδύσεις</option>
                                <option value="Επιδόματα">Επιδόματα</option>
                                <option value="Εφάπαξ / Bonus">Εφάπαξ / Bonus</option>
                                <option value="Πώληση περιουσιακών στοιχείων">Πώληση περιουσιακών στοιχείων</option>
                                <option value="Άλλα">Άλλα</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ποσό Εισοδήματος (€)</label>
                            <input type="text" name="other_incomes[${index}][amount]" class="form-control">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Auto-dismiss success message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(function() {
                    const alert = new bootstrap.Alert(successAlert);
                    alert.close();
                }, 5000); // 5000 milliseconds = 5 seconds
            }
        });

        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()

        // Handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const submissionPeriod = document.querySelector('select[name="submission_period_id"]');
            if (!submissionPeriod.value) {
                e.preventDefault();
                submissionPeriod.classList.add('is-invalid');
                return false;
            }
        });
    