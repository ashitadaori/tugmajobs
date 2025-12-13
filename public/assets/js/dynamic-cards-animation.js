/**
 * Dynamic Cards Animation JavaScript
 * Handles interactive animations, color changes, and dynamic effects for dashboard cards
 */

class DynamicCardsAnimation {
    constructor() {
        this.cards = [];
        this.animationFrameId = null;
        this.isVisible = true;
        this.colorPalettes = {
            primary: ['#667eea', '#764ba2'],
            success: ['#11998e', '#38ef7d'],
            warning: ['#f093fb', '#f5576c'],
            info: ['#4facfe', '#00f2fe'],
            danger: ['#fa709a', '#fee140'],
            purple: ['#a8edea', '#fed6e3'],
            orange: ['#ffecd2', '#fcb69f'],
            blue: ['#a1c4fd', '#c2e9fb'],
            green: ['#fad0c4', '#ffd1ff']
        };
        
        this.init();
    }
    
    init() {
        this.setupCards();
        this.setupCounterAnimations();
        this.setupHoverEffects();
        this.setupParticleEffects();
        this.setupColorCycling();
        this.setupIntersectionObserver();
        this.setupProgressAnimations();
        this.setupDynamicGradients();
        this.startAnimationLoop();
    }
    
    /**
     * Setup card elements and add dynamic classes
     */
    setupCards() {
        const cardSelectors = [
            '.stats-card',
            '.quick-action-card',
            '.welcome-card',
            '.card'
        ];
        
        cardSelectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach((card, index) => {
                this.cards.push({
                    element: card,
                    type: selector.replace('.', ''),
                    index: index,
                    originalTransform: card.style.transform || '',
                    isHovered: false,
                    animationPhase: Math.random() * Math.PI * 2,
                    colorIndex: 0,
                    lastColorChange: Date.now()
                });
                
                // Add dynamic classes
                card.classList.add('card-dynamic', 'card-floating');
                
                // Add particle effects to stats cards
                if (selector === '.stats-card') {
                    card.classList.add('card-particles');
                }
            });
        });
    }
    
    /**
     * Setup counter animations for stats values
     */
    setupCounterAnimations() {
        const counters = document.querySelectorAll('[data-counter]');
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-counter')) || 0;
            const duration = 2000; // 2 seconds
            const increment = target / (duration / 16); // 60fps
            let current = 0;
            
            const updateCounter = () => {
                current += increment;
                if (current >= target) {
                    current = target;
                    counter.textContent = this.formatNumber(target);
                    counter.classList.add('counter-complete');
                    return;
                }
                
                counter.textContent = this.formatNumber(Math.floor(current));
                requestAnimationFrame(updateCounter);
            };
            
            // Start animation when element is visible
            this.observeElement(counter, () => {
                setTimeout(updateCounter, Math.random() * 500);
            });
        });
    }
    
    /**
     * Setup enhanced hover effects
     */
    setupHoverEffects() {
        this.cards.forEach(cardData => {
            const { element } = cardData;
            
            element.addEventListener('mouseenter', (e) => {
                cardData.isHovered = true;
                this.handleCardHover(cardData, e);
            });
            
            element.addEventListener('mouseleave', () => {
                cardData.isHovered = false;
                this.handleCardLeave(cardData);
            });
            
            element.addEventListener('mousemove', (e) => {
                if (cardData.isHovered) {
                    this.handleCardMouseMove(cardData, e);
                }
            });
            
            element.addEventListener('click', (e) => {
                this.handleCardClick(cardData, e);
            });
        });
    }
    
    /**
     * Handle card hover effects
     */
    handleCardHover(cardData, event) {
        const { element } = cardData;
        const rect = element.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        
        // Create ripple effect
        this.createRipple(element, x, y);
        
        // Add glow effect
        element.style.boxShadow = `
            0 20px 40px rgba(0, 0, 0, 0.15),
            0 0 20px rgba(102, 126, 234, 0.3)
        `;
        
        // Enhance icon animation
        const icon = element.querySelector('.stats-icon, .icon-wrapper');
        if (icon) {
            icon.style.transform = 'scale(1.15) rotate(10deg)';
        }
        
        // Animate stats value
        const statsValue = element.querySelector('.stats-value');
        if (statsValue) {
            statsValue.style.transform = 'scale(1.1)';
        }
    }
    
    /**
     * Handle card leave effects
     */
    handleCardLeave(cardData) {
        const { element } = cardData;
        
        // Reset styles
        element.style.boxShadow = '';
        
        const icon = element.querySelector('.stats-icon, .icon-wrapper');
        if (icon) {
            icon.style.transform = '';
        }
        
        const statsValue = element.querySelector('.stats-value');
        if (statsValue) {
            statsValue.style.transform = '';
        }
    }
    
    /**
     * Handle mouse move for 3D tilt effect
     */
    handleCardMouseMove(cardData, event) {
        const { element } = cardData;
        const rect = element.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = (y - centerY) / centerY * -10;
        const rotateY = (x - centerX) / centerX * 10;
        
        element.style.transform = `
            perspective(1000px) 
            rotateX(${rotateX}deg) 
            rotateY(${rotateY}deg) 
            translateY(-8px) 
            scale(1.02)
        `;
    }
    
    /**
     * Handle card click effects
     */
    handleCardClick(cardData, event) {
        const { element } = cardData;
        
        // Create explosion effect
        this.createExplosion(element, event);
        
        // Add click animation
        element.style.transform = 'scale(0.95)';
        setTimeout(() => {
            element.style.transform = '';
        }, 150);
        
        // Trigger stats update animation
        this.triggerStatsUpdate(element);
    }
    
    /**
     * Create ripple effect
     */
    createRipple(element, x, y) {
        const ripple = document.createElement('div');
        const size = Math.max(element.offsetWidth, element.offsetHeight);
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x - size / 2}px;
            top: ${y - size / 2}px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple-expand 0.6s ease-out;
            pointer-events: none;
            z-index: 10;
        `;
        
        element.style.position = 'relative';
        element.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }
    
    /**
     * Create explosion effect
     */
    createExplosion(element, event) {
        const rect = element.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        
        for (let i = 0; i < 12; i++) {
            const particle = document.createElement('div');
            const angle = (i / 12) * Math.PI * 2;
            const velocity = 50 + Math.random() * 50;
            
            particle.style.cssText = `
                position: absolute;
                width: 4px;
                height: 4px;
                left: ${x}px;
                top: ${y}px;
                background: linear-gradient(45deg, #667eea, #764ba2);
                border-radius: 50%;
                pointer-events: none;
                z-index: 20;
                animation: particle-explode 0.8s ease-out forwards;
            `;
            
            particle.style.setProperty('--dx', `${Math.cos(angle) * velocity}px`);
            particle.style.setProperty('--dy', `${Math.sin(angle) * velocity}px`);
            
            element.appendChild(particle);
            setTimeout(() => particle.remove(), 800);
        }
    }
    
    /**
     * Setup particle effects
     */
    setupParticleEffects() {
        const particleCards = document.querySelectorAll('.card-particles');
        
        particleCards.forEach(card => {
            this.createFloatingParticles(card);
        });
    }
    
    /**
     * Create floating particles
     */
    createFloatingParticles(container) {
        const particleCount = 15;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'floating-particle';
            
            particle.style.cssText = `
                position: absolute;
                width: ${2 + Math.random() * 4}px;
                height: ${2 + Math.random() * 4}px;
                background: rgba(255, 255, 255, ${0.1 + Math.random() * 0.3});
                border-radius: 50%;
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                pointer-events: none;
                animation: float-particle ${5 + Math.random() * 10}s linear infinite;
                animation-delay: ${Math.random() * 5}s;
            `;
            
            container.appendChild(particle);
        }
    }
    
    /**
     * Setup color cycling for dynamic themes
     */
    setupColorCycling() {
        const cyclicCards = document.querySelectorAll('.stats-card');
        
        cyclicCards.forEach((card, index) => {
            const cardData = this.cards.find(c => c.element === card);
            if (cardData) {
                cardData.colorCycleInterval = setInterval(() => {
                    this.cycleCardColors(cardData);
                }, 5000 + (index * 1000)); // Stagger the color changes
            }
        });
    }
    
    /**
     * Cycle card colors
     */
    cycleCardColors(cardData) {
        if (cardData.isHovered) return; // Don't change colors while hovering
        
        const { element } = cardData;
        const paletteKeys = Object.keys(this.colorPalettes);
        const currentPalette = paletteKeys[cardData.colorIndex % paletteKeys.length];
        const colors = this.colorPalettes[currentPalette];
        
        const gradient = `linear-gradient(135deg, ${colors[0]} 0%, ${colors[1]} 100%)`;
        
        // Update border gradient
        if (element.classList.contains('gradient-border-primary')) {
            element.style.background = `
                linear-gradient(white, white) padding-box,
                ${gradient} border-box
            `;
        }
        
        // Update icon background
        const icon = element.querySelector('.stats-icon');
        if (icon) {
            icon.style.background = gradient;
        }
        
        // Update progress bar
        const progressBar = element.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.background = gradient;
        }
        
        cardData.colorIndex++;
    }
    
    /**
     * Setup intersection observer for animations
     */
    setupIntersectionObserver() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    this.triggerCardAnimation(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '50px'
        });
        
        this.cards.forEach(cardData => {
            observer.observe(cardData.element);
        });
    }
    
    /**
     * Setup progress bar animations
     */
    setupProgressAnimations() {
        const progressBars = document.querySelectorAll('.progress-bar');
        
        progressBars.forEach(bar => {
            this.observeElement(bar, () => {
                const width = bar.style.width || bar.getAttribute('style')?.match(/width:\s*(\d+%)/)?.[1] || '0%';
                bar.style.width = '0%';
                
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });
        });
    }
    
    /**
     * Setup dynamic gradients
     */
    setupDynamicGradients() {
        const gradientElements = document.querySelectorAll('.stats-value, .welcome-card');
        
        gradientElements.forEach(element => {
            this.animateGradient(element);
        });
    }
    
    /**
     * Animate gradient backgrounds
     */
    animateGradient(element) {
        let hue = 0;
        
        const updateGradient = () => {
            hue = (hue + 1) % 360;
            const color1 = `hsl(${hue}, 70%, 60%)`;
            const color2 = `hsl(${(hue + 60) % 360}, 70%, 60%)`;
            
            if (element.classList.contains('stats-value')) {
                element.style.background = `linear-gradient(135deg, ${color1}, ${color2})`;
                element.style.webkitBackgroundClip = 'text';
                element.style.webkitTextFillColor = 'transparent';
                element.style.backgroundClip = 'text';
            }
        };
        
        setInterval(updateGradient, 100);
    }
    
    /**
     * Start main animation loop
     */
    startAnimationLoop() {
        const animate = () => {
            if (!this.isVisible) {
                this.animationFrameId = requestAnimationFrame(animate);
                return;
            }
            
            this.updateFloatingAnimation();
            this.updateParticles();
            
            this.animationFrameId = requestAnimationFrame(animate);
        };
        
        animate();
    }
    
    /**
     * Update floating animation
     */
    updateFloatingAnimation() {
        const time = Date.now() * 0.001;
        
        this.cards.forEach(cardData => {
            if (cardData.isHovered) return;
            
            const { element, animationPhase } = cardData;
            const offset = Math.sin(time + animationPhase) * 5;
            const rotation = Math.sin(time * 0.5 + animationPhase) * 1;
            
            if (!element.style.transform.includes('perspective')) {
                element.style.transform = `translateY(${offset}px) rotate(${rotation}deg)`;
            }
        });
    }
    
    /**
     * Update particle positions
     */
    updateParticles() {
        const particles = document.querySelectorAll('.floating-particle');
        const time = Date.now() * 0.001;
        
        particles.forEach((particle, index) => {
            const x = Math.sin(time + index) * 10;
            const y = Math.cos(time * 0.7 + index) * 10;
            particle.style.transform = `translate(${x}px, ${y}px)`;
        });
    }
    
    /**
     * Trigger card animation
     */
    triggerCardAnimation(element) {
        element.style.animation = 'none';
        element.offsetHeight; // Trigger reflow
        element.style.animation = 'cardSlideIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
    }
    
    /**
     * Trigger stats update animation
     */
    triggerStatsUpdate(element) {
        const statsValue = element.querySelector('.stats-value');
        const progressBar = element.querySelector('.progress-bar');
        
        if (statsValue) {
            statsValue.style.animation = 'none';
            statsValue.offsetHeight;
            statsValue.style.animation = 'countUp 0.8s ease-out';
        }
        
        if (progressBar) {
            const currentWidth = progressBar.style.width;
            progressBar.style.width = '0%';
            setTimeout(() => {
                progressBar.style.width = currentWidth;
            }, 100);
        }
    }
    
    /**
     * Observe element for intersection
     */
    observeElement(element, callback) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    callback();
                    observer.unobserve(element);
                }
            });
        }, { threshold: 0.1 });
        
        observer.observe(element);
    }
    
    /**
     * Format numbers for display
     */
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }
    
    /**
     * Handle visibility change
     */
    handleVisibilityChange() {
        this.isVisible = !document.hidden;
    }
    
    /**
     * Public methods
     */
    pauseAnimations() {
        this.isVisible = false;
    }
    
    resumeAnimations() {
        this.isVisible = true;
    }
    
    refreshCards() {
        this.cards = [];
        this.setupCards();
    }
    
    updateCardData(cardElement, newData) {
        const cardData = this.cards.find(c => c.element === cardElement);
        if (cardData && newData) {
            // Update counter
            const counter = cardElement.querySelector('[data-counter]');
            if (counter && newData.value) {
                counter.setAttribute('data-counter', newData.value);
                this.setupCounterAnimations();
            }
            
            // Update progress
            const progressBar = cardElement.querySelector('.progress-bar');
            if (progressBar && newData.progress) {
                progressBar.style.width = newData.progress + '%';
            }
        }
    }
    
    destroy() {
        if (this.animationFrameId) {
            cancelAnimationFrame(this.animationFrameId);
        }
        
        this.cards.forEach(cardData => {
            if (cardData.colorCycleInterval) {
                clearInterval(cardData.colorCycleInterval);
            }
        });
    }
}

// Add required CSS animations
const animationCSS = `
@keyframes ripple-expand {
    to {
        transform: scale(2);
        opacity: 0;
    }
}

@keyframes particle-explode {
    to {
        transform: translate(var(--dx), var(--dy));
        opacity: 0;
    }
}

@keyframes float-particle {
    0%, 100% { 
        transform: translateY(0px) rotate(0deg);
        opacity: 0.3;
    }
    50% { 
        transform: translateY(-20px) rotate(180deg);
        opacity: 0.8;
    }
}

@keyframes cardSlideIn {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.animate-in {
    animation: cardSlideIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
`;

// Add CSS to document
const style = document.createElement('style');
style.textContent = animationCSS;
document.head.appendChild(style);

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.dynamicCardsAnimation = new DynamicCardsAnimation();
});

// Handle visibility changes
document.addEventListener('visibilitychange', () => {
    if (window.dynamicCardsAnimation) {
        window.dynamicCardsAnimation.handleVisibilityChange();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DynamicCardsAnimation;
}