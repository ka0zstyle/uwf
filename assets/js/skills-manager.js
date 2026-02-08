/**
 * Skills Manager - Frontend JavaScript
 * Manages the skills modal and CRUD operations
 */
(function() {
    'use strict';

    let currentLang = document.documentElement.lang || 'en';
    let adminPassword = '';

    // Initialize skills manager
    function init() {
        // Add skills manager button to chat controls
        addSkillsButton();
        
        // Load skills when modal is opened
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'skills-manager-btn') {
                openSkillsModal();
            }
        });
    }

    // Add skills management button to chat header
    function addSkillsButton() {
        const chatControls = document.querySelector('.chat-controls');
        if (!chatControls) return;

        const skillsBtn = document.createElement('button');
        skillsBtn.id = 'skills-manager-btn';
        skillsBtn.type = 'button';
        skillsBtn.setAttribute('aria-label', 'Manage Skills');
        skillsBtn.setAttribute('title', currentLang === 'es' ? 'Gestionar Habilidades' : 'Manage Skills');
        skillsBtn.innerHTML = '<i class="fa fa-trophy" aria-hidden="true"></i>';
        skillsBtn.onclick = function(e) { 
            e.stopPropagation(); 
            openSkillsModal(); 
        };

        // Insert before settings button
        const settingsBtn = document.getElementById('toggle-settings');
        if (settingsBtn) {
            chatControls.insertBefore(skillsBtn, settingsBtn);
        } else {
            chatControls.appendChild(skillsBtn);
        }
    }

    // Open skills management modal
    function openSkillsModal() {
        // Check if modal already exists
        let modal = document.getElementById('skills-modal');
        if (!modal) {
            modal = createModal();
            document.body.appendChild(modal);
        }

        // Prompt for password if not set
        if (!adminPassword) {
            const pwd = prompt(currentLang === 'es' ? 
                'Ingrese la contraseña de administrador:' : 
                'Enter admin password:');
            
            if (!pwd) return;
            adminPassword = pwd;
        }

        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        loadSkills();
    }

    // Close skills modal
    function closeSkillsModal() {
        const modal = document.getElementById('skills-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // Create modal HTML
    function createModal() {
        const modal = document.createElement('div');
        modal.id = 'skills-modal';
        modal.className = 'skills-modal';
        modal.innerHTML = `
            <div class="skills-modal-content">
                <div class="skills-modal-header">
                    <h2><i class="fa fa-trophy"></i> ${currentLang === 'es' ? 'Gestión de Habilidades' : 'Skills Management'}</h2>
                    <button class="skills-close" onclick="window.SkillsManager.close()">&times;</button>
                </div>
                <div class="skills-modal-body">
                    <div class="skills-form">
                        <h3>${currentLang === 'es' ? 'Agregar Nueva Habilidad' : 'Add New Skill'}</h3>
                        <form id="add-skill-form">
                            <div class="form-group">
                                <label for="skill-name-en">${currentLang === 'es' ? 'Nombre (Inglés)' : 'Name (English)'}</label>
                                <input type="text" id="skill-name-en" required>
                            </div>
                            <div class="form-group">
                                <label for="skill-name-es">${currentLang === 'es' ? 'Nombre (Español)' : 'Name (Spanish)'}</label>
                                <input type="text" id="skill-name-es" required>
                            </div>
                            <div class="form-group">
                                <label for="skill-percentage">${currentLang === 'es' ? 'Porcentaje (0-100)' : 'Percentage (0-100)'}</label>
                                <input type="number" id="skill-percentage" min="0" max="100" value="80" required>
                            </div>
                            <button type="submit" class="btn-primary">
                                <i class="fa fa-plus"></i> ${currentLang === 'es' ? 'Agregar' : 'Add'}
                            </button>
                        </form>
                    </div>
                    <div class="skills-list">
                        <h3>${currentLang === 'es' ? 'Habilidades Actuales' : 'Current Skills'}</h3>
                        <div id="skills-container">
                            <p class="loading">${currentLang === 'es' ? 'Cargando...' : 'Loading...'}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add event listeners
        setTimeout(() => {
            const form = document.getElementById('add-skill-form');
            if (form) {
                form.addEventListener('submit', handleAddSkill);
            }

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeSkillsModal();
                }
            });
        }, 0);

        return modal;
    }

    // Load skills from server
    async function loadSkills() {
        const container = document.getElementById('skills-container');
        if (!container) return;

        try {
            const response = await fetch('skills_manager.php?action=list');
            const data = await response.json();

            if (data.success && data.skills) {
                displaySkills(data.skills);
            } else {
                container.innerHTML = `<p class="error">${currentLang === 'es' ? 'Error al cargar habilidades' : 'Error loading skills'}</p>`;
            }
        } catch (error) {
            console.error('Error loading skills:', error);
            container.innerHTML = `<p class="error">${currentLang === 'es' ? 'Error de conexión' : 'Connection error'}</p>`;
        }
    }

    // Display skills in the modal
    function displaySkills(skills) {
        const container = document.getElementById('skills-container');
        if (!container) return;

        if (skills.length === 0) {
            container.innerHTML = `<p class="no-skills">${currentLang === 'es' ? 'No hay habilidades registradas' : 'No skills registered'}</p>`;
            return;
        }

        let html = '<div class="skills-grid">';
        skills.forEach(skill => {
            const skillName = currentLang === 'es' ? skill.name_es : skill.name_en;
            html += `
                <div class="skill-card" data-id="${skill.id}">
                    <div class="skill-info">
                        <h4>${skillName}</h4>
                        <div class="skill-percentage-bar">
                            <div class="skill-percentage-fill" style="width: ${skill.percentage}%"></div>
                            <span class="skill-percentage-text">${skill.percentage}%</span>
                        </div>
                        <small>${currentLang === 'es' ? 'EN: ' : ''}${skill.name_en} / ${currentLang === 'es' ? 'ES: ' : ''}${skill.name_es}</small>
                    </div>
                    <div class="skill-actions">
                        <button class="btn-delete" onclick="window.SkillsManager.deleteSkill(${skill.id})" title="${currentLang === 'es' ? 'Eliminar' : 'Delete'}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    // Handle add skill form submission
    async function handleAddSkill(e) {
        e.preventDefault();

        const nameEn = document.getElementById('skill-name-en').value.trim();
        const nameEs = document.getElementById('skill-name-es').value.trim();
        const percentage = document.getElementById('skill-percentage').value;

        if (!nameEn || !nameEs || !percentage) {
            alert(currentLang === 'es' ? 'Por favor complete todos los campos' : 'Please fill all fields');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('password', adminPassword);
        formData.append('name_en', nameEn);
        formData.append('name_es', nameEs);
        formData.append('percentage', percentage);

        try {
            const response = await fetch('skills_manager.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Clear form
                document.getElementById('add-skill-form').reset();
                
                // Reload skills
                loadSkills();
                
                // Show success message
                alert(currentLang === 'es' ? 'Habilidad agregada exitosamente' : 'Skill added successfully');
            } else {
                if (response.status === 401) {
                    adminPassword = '';
                    alert(currentLang === 'es' ? 'Contraseña incorrecta. Por favor intente de nuevo.' : 'Incorrect password. Please try again.');
                    closeSkillsModal();
                } else {
                    alert(data.message || (currentLang === 'es' ? 'Error al agregar habilidad' : 'Error adding skill'));
                }
            }
        } catch (error) {
            console.error('Error adding skill:', error);
            alert(currentLang === 'es' ? 'Error de conexión' : 'Connection error');
        }
    }

    // Delete skill
    async function deleteSkill(id) {
        if (!confirm(currentLang === 'es' ? '¿Está seguro de eliminar esta habilidad?' : 'Are you sure you want to delete this skill?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('password', adminPassword);
        formData.append('id', id);

        try {
            const response = await fetch('skills_manager.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                loadSkills();
                alert(currentLang === 'es' ? 'Habilidad eliminada exitosamente' : 'Skill deleted successfully');
            } else {
                if (response.status === 401) {
                    adminPassword = '';
                    alert(currentLang === 'es' ? 'Contraseña incorrecta' : 'Incorrect password');
                    closeSkillsModal();
                } else {
                    alert(data.message || (currentLang === 'es' ? 'Error al eliminar habilidad' : 'Error deleting skill'));
                }
            }
        } catch (error) {
            console.error('Error deleting skill:', error);
            alert(currentLang === 'es' ? 'Error de conexión' : 'Connection error');
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', init);

    // Expose public API
    window.SkillsManager = {
        close: closeSkillsModal,
        deleteSkill: deleteSkill,
        reload: loadSkills
    };
})();
