// Text-to-Speech (TTS) functionality with voice, speed controls, and text highlighting
(function() {
    'use strict';

    // Check if Speech Synthesis is supported
    if (!('speechSynthesis' in window)) {
        console.warn('Text-to-Speech not supported in this browser');
        return;
    }

    let voices = [];
    let currentUtterance = null;
    let isPaused = false;
    let isReading = false;
    let selectedVoiceIndex = 0;
    let speechRate = 1.0;
    let speechPitch = 1.0;
    let textElements = []; // Store elements to highlight
    let textContents = []; // Store text content for each element
    let currentElementIndex = 0;
    let highlightEnabled = true; // Toggle for highlighting feature
    let isAutoAdvancing = false; // Track if auto-advancing through elements

    // Load voices
    function loadVoices() {
        voices = window.speechSynthesis.getVoices();
        
        // Prefer English voices for better quality
        const englishVoices = voices.filter(v => v.lang.startsWith('en'));
        const tamilVoices = voices.filter(v => v.lang.startsWith('ta'));
        
        // Sort: Tamil first, then English, then others
        voices = [...tamilVoices, ...englishVoices, ...voices.filter(v => !v.lang.startsWith('en') && !v.lang.startsWith('ta'))];
        
        // Try to restore saved voice preference
        const savedVoice = localStorage.getItem('tts_voice_index');
        if (savedVoice !== null && parseInt(savedVoice) < voices.length) {
            selectedVoiceIndex = parseInt(savedVoice);
        } else {
            // Default to first English or Tamil voice if available
            const preferredIndex = voices.findIndex(v => v.lang.startsWith('en') || v.lang.startsWith('ta'));
            if (preferredIndex !== -1) {
                selectedVoiceIndex = preferredIndex;
            }
        }

        // Load saved settings
        const savedRate = localStorage.getItem('tts_rate');
        if (savedRate !== null) {
            speechRate = parseFloat(savedRate);
        }

        const savedPitch = localStorage.getItem('tts_pitch');
        if (savedPitch !== null) {
            speechPitch = parseFloat(savedPitch);
        }

        // Load saved highlight setting
        const savedHighlight = localStorage.getItem('tts_highlight');
        if (savedHighlight !== null) {
            highlightEnabled = savedHighlight === 'true';
        }

        populateVoiceList();
    }

    // Populate voice select dropdown
    function populateVoiceList() {
        const voiceSelect = document.getElementById('ttsVoiceSelect');
        if (!voiceSelect) return;

        voiceSelect.innerHTML = '';
        voices.forEach((voice, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = `${voice.name} (${voice.lang})`;
            if (index === selectedVoiceIndex) {
                option.selected = true;
            }
            voiceSelect.appendChild(option);
        });
    }

    // Extract text content from meditation and collect elements for highlighting
    function getTextToRead() {
        const contentDiv = document.querySelector('.devotion-content');
        if (!contentDiv) return '';

        let textParts = [];
        textElements = []; // Reset elements array
        textContents = []; // Reset text contents array
        
        // Get title
        const title = document.querySelector('.devotion-header h2');
        if (title) {
            const text = title.textContent.trim();
            textParts.push(text);
            textElements.push(title);
            textContents.push(text);
        }

        // Get all sections
        const sections = contentDiv.querySelectorAll('.section');
        sections.forEach(section => {
            const heading = section.querySelector('h2');
            
            if (heading) {
                const text = heading.textContent.trim();
                textParts.push(text);
                textElements.push(heading);
                textContents.push(text);
            }
            
            // Handle different content types
            const paragraphs = section.querySelectorAll('p');
            const verseRef = section.querySelector('.verse-reference');
            const blockquote = section.querySelector('blockquote');
            
            if (paragraphs.length > 0) {
                // Multiple paragraphs - process each separately
                paragraphs.forEach(p => {
                    // Replace <br> tags with periods and spaces for proper pausing
                    let text = p.innerHTML
                        .replace(/<br\s*\/?>/gi, '. ') // Replace <br> with period and space
                        .replace(/<[^>]+>/g, '') // Remove remaining HTML tags
                        .replace(/\s+/g, ' ') // Normalize whitespace
                        .trim();
                    
                    // Clean up multiple periods
                    text = text.replace(/\.{2,}/g, '.');
                    
                    if (text.length > 0) {
                        // For long paragraphs, split into sentences for better pause handling
                        if (text.length > 200) {
                            // Split by sentence-ending punctuation followed by space
                            const sentences = text.match(/[^.!?;]+[.!?;]+[\s]*/g) || [text];
                            sentences.forEach(sentence => {
                                const trimmed = sentence.trim();
                                if (trimmed.length > 0) {
                                    textParts.push(trimmed);
                                    textElements.push(p);
                                    textContents.push(trimmed);
                                }
                            });
                        } else {
                            textParts.push(text);
                            textElements.push(p);
                            textContents.push(text);
                        }
                    }
                });
            } else if (verseRef) {
                const text = verseRef.textContent.trim();
                if (text.length > 0) {
                    textParts.push(text);
                    textElements.push(verseRef);
                    textContents.push(text);
                }
            } else if (blockquote) {
                const text = blockquote.textContent.trim();
                if (text.length > 0) {
                    textParts.push(text);
                    textElements.push(blockquote);
                    textContents.push(text);
                }
            }
        });

        return textParts.filter(text => text.length > 0).join('. ');
    }

    // Highlight current text element
    function highlightElement(index) {
        // Remove previous highlights
        textElements.forEach(el => {
            el.classList.remove('tts-highlight');
        });

        // Add highlight to current element
        if (index >= 0 && index < textElements.length) {
            textElements[index].classList.add('tts-highlight');
            
            // Scroll element into view smoothly
            textElements[index].scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }

    // Remove all highlights
    function removeAllHighlights() {
        textElements.forEach(el => {
            el.classList.remove('tts-highlight');
        });
    }

    // Create TTS settings modal
    function createSettingsModal() {
        const modalHTML = `
            <div class="modal fade" id="ttsSettingsModal" tabindex="-1" aria-labelledby="ttsSettingsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ttsSettingsModalLabel">
                                <i class="fas fa-cog me-2"></i>Read Aloud Settings
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="ttsVoiceSelect" class="form-label">
                                    <i class="fas fa-user me-2"></i>Voice
                                </label>
                                <select class="form-select" id="ttsVoiceSelect">
                                    <option>Loading voices...</option>
                                </select>
                                <small class="form-text text-muted">Select your preferred voice</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ttsRateSlider" class="form-label">
                                    <i class="fas fa-tachometer-alt me-2"></i>Speed: <span id="ttsRateValue">1.0x</span>
                                </label>
                                <input type="range" class="form-range" id="ttsRateSlider" 
                                       min="0.5" max="2.0" step="0.1" value="1.0">
                                <small class="form-text text-muted">Adjust reading speed (0.5x - 2.0x)</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ttsPitchSlider" class="form-label">
                                    <i class="fas fa-music me-2"></i>Pitch: <span id="ttsPitchValue">1.0</span>
                                </label>
                                <input type="range" class="form-range" id="ttsPitchSlider" 
                                       min="0.5" max="2.0" step="0.1" value="1.0">
                                <small class="form-text text-muted">Adjust voice pitch (0.5 - 2.0)</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="ttsHighlightToggle" checked>
                                    <label class="form-check-label" for="ttsHighlightToggle">
                                        <i class="fas fa-highlighter me-2"></i>Highlight Current Text
                                    </label>
                                </div>
                                <small class="form-text text-muted">Show visual highlight as text is being read</small>
                            </div>

                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Tip:</strong> Settings are automatically saved for your next visit.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="ttsTestBtn">
                                <i class="fas fa-play me-2"></i>Test Voice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    // Initialize TTS
    function initTTS() {
        const ttsBtn = document.getElementById('ttsBtn');
        if (!ttsBtn) return;

        // Create settings modal
        createSettingsModal();

        // Load voices
        loadVoices();
        
        // Voices might load async
        if (window.speechSynthesis.onvoiceschanged !== undefined) {
            window.speechSynthesis.onvoiceschanged = loadVoices;
        }

        // TTS button click handler
        ttsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (isReading) {
                if (isPaused) {
                    resumeReading();
                } else {
                    pauseReading();
                }
            } else {
                // Show settings modal on first click, or long press
                const modal = new bootstrap.Modal(document.getElementById('ttsSettingsModal'));
                modal.show();
                
                // Start reading when modal closes
                document.getElementById('ttsSettingsModal').addEventListener('hidden.bs.modal', function handler() {
                    startReading();
                    this.removeEventListener('hidden.bs.modal', handler);
                }, { once: true });
            }
        });

        // Settings button (create a separate button for settings)
        ttsBtn.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('ttsSettingsModal'));
            modal.show();
        });

        // Voice selection
        const voiceSelect = document.getElementById('ttsVoiceSelect');
        if (voiceSelect) {
            voiceSelect.addEventListener('change', function() {
                selectedVoiceIndex = parseInt(this.value);
                localStorage.setItem('tts_voice_index', selectedVoiceIndex);
            });
        }

        // Rate slider
        const rateSlider = document.getElementById('ttsRateSlider');
        const rateValue = document.getElementById('ttsRateValue');
        if (rateSlider && rateValue) {
            rateSlider.value = speechRate;
            rateValue.textContent = speechRate.toFixed(1) + 'x';
            
            rateSlider.addEventListener('input', function() {
                speechRate = parseFloat(this.value);
                rateValue.textContent = speechRate.toFixed(1) + 'x';
                localStorage.setItem('tts_rate', speechRate);
            });
        }

        // Pitch slider
        const pitchSlider = document.getElementById('ttsPitchSlider');
        const pitchValue = document.getElementById('ttsPitchValue');
        if (pitchSlider && pitchValue) {
            pitchSlider.value = speechPitch;
            pitchValue.textContent = speechPitch.toFixed(1);
            
            pitchSlider.addEventListener('input', function() {
                speechPitch = parseFloat(this.value);
                pitchValue.textContent = speechPitch.toFixed(1);
                localStorage.setItem('tts_pitch', speechPitch);
            });
        }

        // Highlight toggle
        const highlightToggle = document.getElementById('ttsHighlightToggle');
        if (highlightToggle) {
            highlightToggle.checked = highlightEnabled;
            
            highlightToggle.addEventListener('change', function() {
                highlightEnabled = this.checked;
                localStorage.setItem('tts_highlight', highlightEnabled);
                
                // Remove highlights if disabled while reading
                if (!highlightEnabled && isReading) {
                    removeAllHighlights();
                }
            });
        }

        // Test button
        const testBtn = document.getElementById('ttsTestBtn');
        if (testBtn) {
            testBtn.addEventListener('click', function() {
                stopReading();
                const testText = "Hello, this is a test of the text to speech voice. How does it sound?";
                speakText(testText, false); // Disable highlighting for test
            });
        }
    }

    // Speak text with highlighting - improved timing
    function speakText(text, useHighlight = null) {
        if (!text) return;
        
        // Use parameter if provided, otherwise use global setting
        const shouldHighlight = useHighlight !== null ? useHighlight : highlightEnabled;

        // Cancel any ongoing speech
        window.speechSynthesis.cancel();

        currentUtterance = new SpeechSynthesisUtterance(text);
        
        if (voices[selectedVoiceIndex]) {
            currentUtterance.voice = voices[selectedVoiceIndex];
        }
        
        currentUtterance.rate = speechRate;
        currentUtterance.pitch = speechPitch;
        
        // Set language to help with pronunciation and pauses
        currentUtterance.lang = voices[selectedVoiceIndex] ? voices[selectedVoiceIndex].lang : 'en-US';
        
        currentUtterance.onstart = function() {
            updateButton('pause');
        };
        
        currentUtterance.onend = function() {
            if (shouldHighlight) {
                removeAllHighlights();
            }
            stopReading();
        };
        
        currentUtterance.onerror = function(event) {
            console.error('Speech synthesis error:', event);
            if (shouldHighlight) {
                removeAllHighlights();
            }
            stopReading();
        };

        window.speechSynthesis.speak(currentUtterance);
    }

    // Speak element by element for better highlighting sync
    function speakElementByElement(index = 0) {
        if (index >= textContents.length) {
            // Finished reading all elements
            stopReading();
            return;
        }

        // Highlight current element
        if (highlightEnabled) {
            highlightElement(index);
        }

        currentElementIndex = index;
        let text = textContents[index];
        
        if (!text) {
            // Skip empty content
            speakElementByElement(index + 1);
            return;
        }

        // Ensure text ends with proper punctuation for natural pause
        text = text.trim();
        
        // For Tamil and other languages, enhance punctuation pauses
        // Add slight pause after commas and periods by inserting zero-width space + comma/period
        text = text.replace(/,(\s*)/g, ',  '); // Double space after comma
        text = text.replace(/\.(\s*)/g, '.  '); // Double space after period
        text = text.replace(/;(\s*)/g, ';  '); // Double space after semicolon
        
        if (!text.match(/[.!?,;:]$/)) {
            text += '.';
        }

        // Cancel any ongoing speech
        window.speechSynthesis.cancel();

        currentUtterance = new SpeechSynthesisUtterance(text);
        
        if (voices[selectedVoiceIndex]) {
            currentUtterance.voice = voices[selectedVoiceIndex];
            // Set language to help with pronunciation and pauses
            currentUtterance.lang = voices[selectedVoiceIndex].lang;
        } else {
            // Default language
            currentUtterance.lang = 'ta-IN'; // Tamil India as fallback
        }
        
        currentUtterance.rate = speechRate;
        currentUtterance.pitch = speechPitch;
        
        // Increase volume for better clarity
        currentUtterance.volume = 1.0;
        
        currentUtterance.onstart = function() {
            updateButton('pause');
            isAutoAdvancing = true;
        };
        
        currentUtterance.onend = function() {
            if (isReading && !isPaused) {
                // Add a small pause between elements (400ms for better pacing with Tamil)
                setTimeout(function() {
                    if (isReading && !isPaused) {
                        speakElementByElement(index + 1);
                    }
                }, 400);
            } else if (isPaused) {
                // Keep current index when paused
                currentElementIndex = index;
            }
        };
        
        currentUtterance.onerror = function(event) {
            console.error('Speech synthesis error:', event);
            stopReading();
        };

        window.speechSynthesis.speak(currentUtterance);
    }

    // Start reading
    function startReading() {
        getTextToRead(); // This populates textElements and textContents
        
        if (textContents.length === 0) {
            alert('No content available to read.');
            return;
        }

        isReading = true;
        isPaused = false;
        isAutoAdvancing = false;
        currentElementIndex = 0;
        
        // Start reading from first element
        speakElementByElement(0);
    }

    // Pause reading
    function pauseReading() {
        if (window.speechSynthesis.speaking && !isPaused) {
            window.speechSynthesis.pause();
            isPaused = true;
            updateButton('resume');
        }
    }

    // Resume reading
    function resumeReading() {
        if (isPaused) {
            isPaused = false;
            
            // Check if there's an active utterance to resume
            if (window.speechSynthesis.paused) {
                window.speechSynthesis.resume();
                updateButton('pause');
            } else {
                // If no active utterance, restart from current element
                if (currentElementIndex < textContents.length) {
                    isReading = true;
                    speakElementByElement(currentElementIndex);
                }
            }
        }
    }

    // Stop reading
    function stopReading() {
        window.speechSynthesis.cancel();
        isReading = false;
        isPaused = false;
        isAutoAdvancing = false;
        currentUtterance = null;
        currentElementIndex = 0;
        removeAllHighlights();
        updateButton('play');
    }

    // Update button appearance
    function updateButton(state) {
        const ttsBtn = document.getElementById('ttsBtn');
        if (!ttsBtn) return;

        const icon = ttsBtn.querySelector('i');
        
        switch (state) {
            case 'play':
                icon.className = 'fas fa-volume-up';
                ttsBtn.title = 'Read Aloud';
                ttsBtn.classList.remove('active');
                break;
            case 'pause':
                icon.className = 'fas fa-pause';
                ttsBtn.title = 'Pause Reading';
                ttsBtn.classList.add('active');
                break;
            case 'resume':
                icon.className = 'fas fa-play';
                ttsBtn.title = 'Resume Reading';
                ttsBtn.classList.add('active');
                break;
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTTS);
    } else {
        initTTS();
    }

    // Stop reading when navigating away
    window.addEventListener('beforeunload', function() {
        stopReading();
    });

})();
