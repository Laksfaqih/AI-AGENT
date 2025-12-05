/**
 * Indonesia AI Agent - Theme System
 * Provides multiple color schemes with easy theme switching
 */

const THEMES = {
    default: {
        name: 'Purple Gradient',
        icon: '🟣',
        colors: {
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            headerBg: 'rgba(255, 255, 255, 0.1)',
            headerBorder: 'rgba(255, 255, 255, 0.2)',
            cardBg: 'rgba(255, 255, 255, 0.15)',
            cardBorder: 'rgba(255, 255, 255, 0.25)',
            inputBg: 'rgba(255, 255, 255, 0.2)',
            inputBorder: 'rgba(255, 255, 255, 0.3)',
            buttonBg: 'linear-gradient(135deg, #667eea, #764ba2)',
            buttonHover: 'linear-gradient(135deg, #5568d3, #653a8b)',
            text: '#ffffff',
            textSecondary: 'rgba(255, 255, 255, 0.9)',
            accent: '#667eea',
            accentLight: '#8b9bef'
        }
    },
    ocean: {
        name: 'Ocean Blue',
        icon: '🌊',
        colors: {
            background: 'linear-gradient(135deg, #0093E9 0%, #80D0C7 100%)',
            headerBg: 'rgba(255, 255, 255, 0.1)',
            headerBorder: 'rgba(255, 255, 255, 0.2)',
            cardBg: 'rgba(255, 255, 255, 0.15)',
            cardBorder: 'rgba(255, 255, 255, 0.25)',
            inputBg: 'rgba(255, 255, 255, 0.2)',
            inputBorder: 'rgba(255, 255, 255, 0.3)',
            buttonBg: 'linear-gradient(135deg, #0093E9, #00b4d8)',
            buttonHover: 'linear-gradient(135deg, #0077b6, #0096c7)',
            text: '#ffffff',
            textSecondary: 'rgba(255, 255, 255, 0.9)',
            accent: '#0093E9',
            accentLight: '#48cae4'
        }
    },
    sunset: {
        name: 'Sunset Vibes',
        icon: '🌅',
        colors: {
            background: 'linear-gradient(135deg, #FA8BFF 0%, #2BD2FF 50%, #2BFF88 100%)',
            headerBg: 'rgba(255, 255, 255, 0.1)',
            headerBorder: 'rgba(255, 255, 255, 0.2)',
            cardBg: 'rgba(255, 255, 255, 0.15)',
            cardBorder: 'rgba(255, 255, 255, 0.25)',
            inputBg: 'rgba(255, 255, 255, 0.2)',
            inputBorder: 'rgba(255, 255, 255, 0.3)',
            buttonBg: 'linear-gradient(135deg, #FA8BFF, #2BD2FF)',
            buttonHover: 'linear-gradient(135deg, #e877ec, #24b9e6)',
            text: '#ffffff',
            textSecondary: 'rgba(255, 255, 255, 0.9)',
            accent: '#FA8BFF',
            accentLight: '#ff9fdb'
        }
    },
    forest: {
        name: 'Forest Green',
        icon: '🌲',
        colors: {
            background: 'linear-gradient(135deg, #134E5E 0%, #71B280 100%)',
            headerBg: 'rgba(255, 255, 255, 0.1)',
            headerBorder: 'rgba(255, 255, 255, 0.2)',
            cardBg: 'rgba(255, 255, 255, 0.15)',
            cardBorder: 'rgba(255, 255, 255, 0.25)',
            inputBg: 'rgba(255, 255, 255, 0.2)',
            inputBorder: 'rgba(255, 255, 255, 0.3)',
            buttonBg: 'linear-gradient(135deg, #2d6a4f, #52b788)',
            buttonHover: 'linear-gradient(135deg, #1b4332, #40916c)',
            text: '#ffffff',
            textSecondary: 'rgba(255, 255, 255, 0.9)',
            accent: '#52b788',
            accentLight: '#74c69d'
        }
    },
    dark: {
        name: 'Dark Mode',
        icon: '🌙',
        colors: {
            background: 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)',
            headerBg: 'rgba(255, 255, 255, 0.05)',
            headerBorder: 'rgba(255, 255, 255, 0.1)',
            cardBg: 'rgba(255, 255, 255, 0.08)',
            cardBorder: 'rgba(255, 255, 255, 0.15)',
            inputBg: 'rgba(255, 255, 255, 0.1)',
            inputBorder: 'rgba(255, 255, 255, 0.2)',
            buttonBg: 'linear-gradient(135deg, #0f3460, #16213e)',
            buttonHover: 'linear-gradient(135deg, #0a2647, #0e1b2e)',
            text: '#e5e5e5',
            textSecondary: 'rgba(229, 229, 229, 0.8)',
            accent: '#e94560',
            accentLight: '#f27895'
        }
    },
    coral: {
        name: 'Coral Reef',
        icon: '🪸',
        colors: {
            background: 'linear-gradient(135deg, #FF6B6B 0%, #FFE66D 100%)',
            headerBg: 'rgba(255, 255, 255, 0.1)',
            headerBorder: 'rgba(255, 255, 255, 0.2)',
            cardBg: 'rgba(255, 255, 255, 0.15)',
            cardBorder: 'rgba(255, 255, 255, 0.25)',
            inputBg: 'rgba(255, 255, 255, 0.2)',
            inputBorder: 'rgba(255, 255, 255, 0.3)',
            buttonBg: 'linear-gradient(135deg, #FF6B6B, #ff8787)',
            buttonHover: 'linear-gradient(135deg, #fa5252, #ff6b6b)',
            text: '#ffffff',
            textSecondary: 'rgba(255, 255, 255, 0.9)',
            accent: '#FF6B6B',
            accentLight: '#ff8787'
        }
    },
    lavender: {
        name: 'Lavender Dreams',
        icon: '💜',
        colors: {
            background: 'linear-gradient(135deg, #A8EDEA 0%, #FED6E3 100%)',
            headerBg: 'rgba(255, 255, 255, 0.2)',
            headerBorder: 'rgba(255, 255, 255, 0.3)',
            cardBg: 'rgba(255, 255, 255, 0.25)',
            cardBorder: 'rgba(255, 255, 255, 0.35)',
            inputBg: 'rgba(255, 255, 255, 0.3)',
            inputBorder: 'rgba(255, 255, 255, 0.4)',
            buttonBg: 'linear-gradient(135deg, #d4a5a5, #ffccd5)',
            buttonHover: 'linear-gradient(135deg, #c69191, #fbb1c0)',
            text: '#2d3748',
            textSecondary: 'rgba(45, 55, 72, 0.8)',
            accent: '#d4a5a5',
            accentLight: '#e6b8b8'
        }
    },
    midnight: {
        name: 'Midnight Blue',
        icon: '🌌',
        colors: {
            background: 'linear-gradient(135deg, #2C3E50 0%, #4CA1AF 100%)',
            headerBg: 'rgba(255, 255, 255, 0.1)',
            headerBorder: 'rgba(255, 255, 255, 0.2)',
            cardBg: 'rgba(255, 255, 255, 0.15)',
            cardBorder: 'rgba(255, 255, 255, 0.25)',
            inputBg: 'rgba(255, 255, 255, 0.2)',
            inputBorder: 'rgba(255, 255, 255, 0.3)',
            buttonBg: 'linear-gradient(135deg, #2C3E50, #3d5a6c)',
            buttonHover: 'linear-gradient(135deg, #1a252f, #2d4553)',
            text: '#ffffff',
            textSecondary: 'rgba(255, 255, 255, 0.9)',
            accent: '#4CA1AF',
            accentLight: '#6bb3bf'
        }
    }
};

class ThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('selectedTheme') || 'default';
        this.init();
    }

    init() {
        this.applyTheme(this.currentTheme);
        this.createThemeSwitcher();
    }

    applyTheme(themeName) {
        const theme = THEMES[themeName];
        if (!theme) return;

        const colors = theme.colors;
        const root = document.documentElement;

        // Apply CSS variables
        root.style.setProperty('--bg-gradient', colors.background);
        root.style.setProperty('--header-bg', colors.headerBg);
        root.style.setProperty('--header-border', colors.headerBorder);
        root.style.setProperty('--card-bg', colors.cardBg);
        root.style.setProperty('--card-border', colors.cardBorder);
        root.style.setProperty('--input-bg', colors.inputBg);
        root.style.setProperty('--input-border', colors.inputBorder);
        root.style.setProperty('--button-bg', colors.buttonBg);
        root.style.setProperty('--button-hover', colors.buttonHover);
        root.style.setProperty('--text-color', colors.text);
        root.style.setProperty('--text-secondary', colors.textSecondary);
        root.style.setProperty('--accent-color', colors.accent);
        root.style.setProperty('--accent-light', colors.accentLight);

        this.currentTheme = themeName;
        localStorage.setItem('selectedTheme', themeName);
        
        // Update active theme in switcher
        this.updateActiveThem();
    }

    createThemeSwitcher() {
        const switcher = document.createElement('div');
        switcher.className = 'theme-switcher';
        switcher.innerHTML = `
            <button class="theme-toggle-btn" title="Change Theme">
                <span class="theme-icon">🎨</span>
            </button>
            <div class="theme-dropdown">
                ${Object.entries(THEMES).map(([key, theme]) => `
                    <button class="theme-option ${key === this.currentTheme ? 'active' : ''}" 
                            data-theme="${key}">
                        <span class="theme-icon">${theme.icon}</span>
                        <span class="theme-name">${theme.name}</span>
                    </button>
                `).join('')}
            </div>
        `;

        // Add to header controls
        const headerControls = document.querySelector('.header-controls');
        if (headerControls) {
            headerControls.insertBefore(switcher, headerControls.firstChild);
        }

        // Add event listeners
        const toggleBtn = switcher.querySelector('.theme-toggle-btn');
        const dropdown = switcher.querySelector('.theme-dropdown');
        
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', () => {
            dropdown.classList.remove('show');
        });

        // Theme option click handlers
        switcher.querySelectorAll('.theme-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const themeName = option.dataset.theme;
                this.applyTheme(themeName);
                dropdown.classList.remove('show');
            });
        });
    }

    updateActiveTheme() {
        document.querySelectorAll('.theme-option').forEach(option => {
            if (option.dataset.theme === this.currentTheme) {
                option.classList.add('active');
            } else {
                option.classList.remove('active');
            }
        });
    }
}

// Initialize theme manager when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.themeManager = new ThemeManager();
    });
} else {
    window.themeManager = new ThemeManager();
}
