// Preliminary Questions Management
document.addEventListener('DOMContentLoaded', function() {
    const requiresScreeningCheckbox = document.getElementById('requires_screening');
    const questionsContainer = document.getElementById('preliminary_questions_container');
    const addQuestionBtn = document.getElementById('add_question_btn');
    const questionsList = document.getElementById('questions_list');
    const hiddenInput = document.getElementById('preliminary_questions');
    
    let questionCounter = 0;
    let questions = [];
    
    // Load existing questions if any (for edit mode)
    const existingQuestions = hiddenInput.value ? JSON.parse(hiddenInput.value) : [];
    if (existingQuestions.length > 0) {
        existingQuestions.forEach(question => {
            addQuestion(question);
        });
        requiresScreeningCheckbox.checked = true;
        questionsContainer.style.display = 'block';
        questionsContainer.classList.add('active');
    }
    
    // Toggle questions container
    requiresScreeningCheckbox.addEventListener('change', function() {
        if (this.checked) {
            questionsContainer.style.display = 'block';
            questionsContainer.classList.add('active');
            // Add a default question if none exist
            if (questions.length === 0) {
                addQuestion();
            }
        } else {
            questionsContainer.style.display = 'none';
            questionsContainer.classList.remove('active');
            // Clear all questions
            questions = [];
            questionsList.innerHTML = '';
            updateHiddenInput();
        }
    });
    
    // Add new question
    addQuestionBtn.addEventListener('click', function() {
        addQuestion();
    });
    
    function addQuestion(existingQuestion = null) {
        questionCounter++;
        const questionId = existingQuestion ? existingQuestion.id : 'q' + Date.now();
        const questionType = existingQuestion ? existingQuestion.type : 'text';
        const questionText = existingQuestion ? existingQuestion.question : '';
        const questionRequired = existingQuestion ? existingQuestion.required : true;
        const questionOptions = existingQuestion ? existingQuestion.options : [];
        
        const questionElement = document.createElement('div');
        questionElement.className = 'question-item';
        questionElement.dataset.questionId = questionId;
        
        questionElement.innerHTML = `
            <div class="question-header">
                <div class="question-number">${questionCounter}</div>
                <div class="flex-grow-1">Question ${questionCounter}</div>
            </div>
            <button type="button" class="remove-question" onclick="removeQuestion('${questionId}')">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="question-type-selector">
                <label class="form-label">Question Type:</label>
                <select class="form-select question-type" data-question-id="${questionId}" onchange="changeQuestionType('${questionId}', this.value)">
                    <option value="text" ${questionType === 'text' ? 'selected' : ''}>Text Input</option>
                    <option value="textarea" ${questionType === 'textarea' ? 'selected' : ''}>Long Text</option>
                    <option value="radio" ${questionType === 'radio' ? 'selected' : ''}>Multiple Choice (Single)</option>
                    <option value="checkbox" ${questionType === 'checkbox' ? 'selected' : ''}>Multiple Choice (Multiple)</option>
                    <option value="select" ${questionType === 'select' ? 'selected' : ''}>Dropdown</option>
                </select>
            </div>
            
            <div class="question-input">
                <label class="form-label">Question:</label>
                <textarea class="form-control question-text" rows="2" 
                          placeholder="Enter your question..." 
                          data-question-id="${questionId}"
                          oninput="updateQuestion('${questionId}')">${questionText}</textarea>
            </div>
            
            <div class="form-check mt-2">
                <input class="form-check-input question-required" type="checkbox" 
                       data-question-id="${questionId}" 
                       onchange="updateQuestion('${questionId}')"
                       ${questionRequired ? 'checked' : ''}>
                <label class="form-check-label">
                    Required question
                </label>
            </div>
            
            <div class="question-options" id="options-${questionId}" style="display: none;">
                <label class="form-label">Answer Options:</label>
                <div class="options-list" id="options-list-${questionId}">
                    <!-- Options will be added here -->
                </div>
                <button type="button" class="add-option-btn" onclick="addOption('${questionId}')">
                    <i class="fas fa-plus"></i> Add Option
                </button>
            </div>
        `;
        
        questionsList.appendChild(questionElement);
        
        // Initialize question object
        const questionObj = {
            id: questionId,
            type: questionType,
            question: questionText,
            required: questionRequired,
            options: questionOptions
        };
        
        questions.push(questionObj);
        
        // Show options if question type requires them
        if (['radio', 'checkbox', 'select'].includes(questionType)) {
            document.getElementById(`options-${questionId}`).style.display = 'block';
            if (questionOptions.length === 0) {
                addOption(questionId);
                addOption(questionId);
            } else {
                questionOptions.forEach((option, index) => {
                    addOption(questionId, option);
                });
            }
        }
        
        updateHiddenInput();
    }
    
    window.removeQuestion = function(questionId) {
        const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
        if (questionElement) {
            questionElement.remove();
            questions = questions.filter(q => q.id !== questionId);
            updateQuestionNumbers();
            updateHiddenInput();
        }
    };
    
    window.changeQuestionType = function(questionId, newType) {
        const question = questions.find(q => q.id === questionId);
        if (question) {
            question.type = newType;
            question.options = [];
            
            const optionsContainer = document.getElementById(`options-${questionId}`);
            const optionsList = document.getElementById(`options-list-${questionId}`);
            
            if (['radio', 'checkbox', 'select'].includes(newType)) {
                optionsContainer.style.display = 'block';
                optionsList.innerHTML = '';
                addOption(questionId);
                addOption(questionId);
            } else {
                optionsContainer.style.display = 'none';
            }
            
            updateHiddenInput();
        }
    };
    
    window.updateQuestion = function(questionId) {
        const question = questions.find(q => q.id === questionId);
        if (question) {
            const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
            const questionText = questionElement.querySelector('.question-text').value;
            const questionRequired = questionElement.querySelector('.question-required').checked;
            
            question.question = questionText;
            question.required = questionRequired;
            
            updateHiddenInput();
        }
    };
    
    window.addOption = function(questionId, optionText = '') {
        const question = questions.find(q => q.id === questionId);
        if (question) {
            const optionId = 'opt' + Date.now() + Math.random().toString(36).substr(2, 5);
            const optionsList = document.getElementById(`options-list-${questionId}`);
            
            const optionElement = document.createElement('div');
            optionElement.className = 'option-item';
            optionElement.dataset.optionId = optionId;
            
            optionElement.innerHTML = `
                <input type="text" class="form-control" placeholder="Option text" 
                       value="${optionText}"
                       oninput="updateOption('${questionId}', '${optionId}', this.value)">
                <button type="button" class="remove-option" onclick="removeOption('${questionId}', '${optionId}')">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            optionsList.appendChild(optionElement);
            
            question.options.push({
                id: optionId,
                text: optionText
            });
            
            updateHiddenInput();
        }
    };
    
    window.removeOption = function(questionId, optionId) {
        const question = questions.find(q => q.id === questionId);
        if (question) {
            const optionElement = document.querySelector(`[data-option-id="${optionId}"]`);
            if (optionElement) {
                optionElement.remove();
                question.options = question.options.filter(opt => opt.id !== optionId);
                updateHiddenInput();
            }
        }
    };
    
    window.updateOption = function(questionId, optionId, optionText) {
        const question = questions.find(q => q.id === questionId);
        if (question) {
            const option = question.options.find(opt => opt.id === optionId);
            if (option) {
                option.text = optionText;
                updateHiddenInput();
            }
        }
    };
    
    function updateQuestionNumbers() {
        const questionElements = document.querySelectorAll('.question-item');
        questionElements.forEach((element, index) => {
            const questionNumber = element.querySelector('.question-number');
            const questionTitle = element.querySelector('.flex-grow-1');
            questionNumber.textContent = index + 1;
            questionTitle.textContent = `Question ${index + 1}`;
        });
        questionCounter = questionElements.length;
    }
    
    function updateHiddenInput() {
        hiddenInput.value = JSON.stringify(questions);
    }
    
    // Validate questions before form submission
    function validateQuestions() {
        if (!requiresScreeningCheckbox.checked) {
            return true; // No validation needed if screening is disabled
        }
        
        let isValid = true;
        const errors = [];
        
        questions.forEach((question, index) => {
            if (!question.question.trim()) {
                errors.push(`Question ${index + 1}: Question text is required`);
                isValid = false;
            }
            
            if (['radio', 'checkbox', 'select'].includes(question.type)) {
                const validOptions = question.options.filter(opt => opt.text.trim());
                if (validOptions.length < 2) {
                    errors.push(`Question ${index + 1}: At least 2 options are required for ${question.type} questions`);
                    isValid = false;
                }
            }
        });
        
        if (!isValid) {
            alert('Please fix the following issues with your questions:\n\n' + errors.join('\n'));
        }
        
        return isValid;
    }
    
    // Add validation to form submission
    const jobForm = document.getElementById('jobForm');
    if (jobForm) {
        jobForm.addEventListener('submit', function(e) {
            if (!validateQuestions()) {
                e.preventDefault();
                return false;
            }
        });
    }
});
