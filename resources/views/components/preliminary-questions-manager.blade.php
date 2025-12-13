<!-- Preliminary Questions Manager Component -->
<div class="preliminary-questions-section">
    <div class="section-header">
        <h5><i class="fas fa-question-circle me-2"></i>Preliminary Interview Questions</h5>
        <p class="text-muted">Create screening questions to help filter applicants before reviewing their full applications</p>
    </div>
    
    <div class="form-group mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="requires_screening" name="requires_screening" value="1" 
                   {{ old('requires_screening', $job->requires_screening ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="requires_screening">
                <strong>Enable Preliminary Screening Questions</strong>
                <div class="small text-muted">Require applicants to answer screening questions before submitting their applications</div>
            </label>
        </div>
    </div>

    <div id="questions-container" class="questions-container" style="{{ old('requires_screening', $job->requires_screening ?? false) ? 'display: block;' : 'display: none;' }}">
        <div class="questions-list" id="questions-list">
            @php
                $questions = old('preliminary_questions', $job->preliminary_questions ?? []);
            @endphp
            
            @if(!empty($questions))
                @foreach($questions as $index => $question)
                    <div class="question-item" data-index="{{ $index }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="mb-0">Question {{ $index + 1 }}</h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-question" onclick="removeQuestion({{ $index }})">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="question_text_{{ $index }}" class="form-label">Question Text</label>
                                    <textarea class="form-control" id="question_text_{{ $index }}" 
                                              name="preliminary_questions[{{ $index }}][question]" 
                                              rows="2" placeholder="Enter your question here..." required>{{ $question['question'] ?? '' }}</textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="question_type_{{ $index }}" class="form-label">Question Type</label>
                                            <select class="form-select question-type-select" id="question_type_{{ $index }}" 
                                                    name="preliminary_questions[{{ $index }}][type]" required onchange="toggleQuestionOptions({{ $index }}, this.value)">
                                                <option value="text" {{ ($question['type'] ?? '') == 'text' ? 'selected' : '' }}>Text Answer</option>
                                                <option value="yes_no" {{ ($question['type'] ?? '') == 'yes_no' ? 'selected' : '' }}>Yes/No</option>
                                                <option value="multiple_choice" {{ ($question['type'] ?? '') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="question_required_{{ $index }}" 
                                                       name="preliminary_questions[{{ $index }}][required]" value="1" 
                                                       {{ ($question['required'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="question_required_{{ $index }}">
                                                    Required Question
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="multiple-choice-options" id="options_container_{{ $index }}" 
                                     style="display: {{ ($question['type'] ?? '') == 'multiple_choice' ? 'block' : 'none' }};">
                                    <label class="form-label">Answer Options</label>
                                    <div class="options-list" id="options_list_{{ $index }}">
                                        @if(($question['type'] ?? '') == 'multiple_choice' && !empty($question['options']))
                                            @foreach($question['options'] as $optionIndex => $option)
                                                <div class="option-item input-group mb-2" data-option-index="{{ $optionIndex }}">
                                                    <input type="text" class="form-control" 
                                                           name="preliminary_questions[{{ $index }}][options][]" 
                                                           value="{{ $option }}" 
                                                           placeholder="Option {{ $optionIndex + 1 }}">
                                                    <button type="button" class="btn btn-outline-danger" onclick="removeOption({{ $index }}, {{ $optionIndex }})">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="option-item input-group mb-2" data-option-index="0">
                                                <input type="text" class="form-control" 
                                                       name="preliminary_questions[{{ $index }}][options][]" 
                                                       value="" 
                                                       placeholder="Option 1">
                                                <button type="button" class="btn btn-outline-danger" onclick="removeOption({{ $index }}, 0)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="option-item input-group mb-2" data-option-index="1">
                                                <input type="text" class="form-control" 
                                                       name="preliminary_questions[{{ $index }}][options][]" 
                                                       value="" 
                                                       placeholder="Option 2">
                                                <button type="button" class="btn btn-outline-danger" onclick="removeOption({{ $index }}, 1)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOption({{ $index }})">
                                        <i class="fas fa-plus me-1"></i>Add Option
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        
        <div class="text-center mt-3">
            <button type="button" class="btn btn-outline-primary" id="add-question-btn">
                <i class="fas fa-plus me-2"></i>Add Question
            </button>
        </div>
        
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Tips for effective screening questions:</strong>
            <ul class="mb-0 mt-2">
                <li>Ask about specific qualifications or experience relevant to the role</li>
                <li>Use yes/no questions to quickly filter candidates</li>
                <li>Keep questions clear and concise</li>
                <li>Consider asking about availability, location preferences, or salary expectations</li>
            </ul>
        </div>
    </div>
</div>

<style>
.preliminary-questions-section {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    background: #f8f9fa;
    margin-bottom: 1.5rem;
}

.section-header {
    margin-bottom: 1.5rem;
}

.section-header h5 {
    color: #495057;
    margin-bottom: 0.5rem;
}

.questions-container {
    margin-top: 1rem;
}

.question-item {
    margin-bottom: 1.5rem;
}

.question-item .card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
}

.question-item .card-body {
    padding: 1.25rem;
}

.option-item {
    position: relative;
}

.option-item .btn-outline-danger {
    border-left: 0;
}

.multiple-choice-options {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
    margin-top: 1rem;
}

.remove-question {
    opacity: 0.7;
    transition: opacity 0.2s;
}

.remove-question:hover {
    opacity: 1;
}

#add-question-btn {
    padding: 0.75rem 1.5rem;
}
</style>

<script>
let questionIndex = {{ count($questions ?? []) }};

// Toggle screening questions visibility
document.getElementById('requires_screening').addEventListener('change', function() {
    const container = document.getElementById('questions-container');
    if (this.checked) {
        container.style.display = 'block';
        if (questionIndex === 0) {
            addQuestion();
        }
    } else {
        container.style.display = 'none';
    }
});

// Add new question
document.getElementById('add-question-btn').addEventListener('click', function() {
    addQuestion();
});

function addQuestion() {
    const questionsList = document.getElementById('questions-list');
    const newQuestionHTML = `
        <div class="question-item" data-index="${questionIndex}">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="mb-0">Question ${questionIndex + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-question" onclick="removeQuestion(${questionIndex})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <label for="question_text_${questionIndex}" class="form-label">Question Text</label>
                        <textarea class="form-control" id="question_text_${questionIndex}" 
                                  name="preliminary_questions[${questionIndex}][question]" 
                                  rows="2" placeholder="Enter your question here..." required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="question_type_${questionIndex}" class="form-label">Question Type</label>
                                <select class="form-select question-type-select" id="question_type_${questionIndex}" 
                                        name="preliminary_questions[${questionIndex}][type]" required onchange="toggleQuestionOptions(${questionIndex}, this.value)">
                                    <option value="text">Text Answer</option>
                                    <option value="yes_no">Yes/No</option>
                                    <option value="multiple_choice">Multiple Choice</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="question_required_${questionIndex}" 
                                           name="preliminary_questions[${questionIndex}][required]" value="1">
                                    <label class="form-check-label" for="question_required_${questionIndex}">
                                        Required Question
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="multiple-choice-options" id="options_container_${questionIndex}" style="display: none;">
                        <label class="form-label">Answer Options</label>
                        <div class="options-list" id="options_list_${questionIndex}">
                            <div class="option-item input-group mb-2" data-option-index="0">
                                <input type="text" class="form-control" 
                                       name="preliminary_questions[${questionIndex}][options][]" 
                                       value="" 
                                       placeholder="Option 1">
                                <button type="button" class="btn btn-outline-danger" onclick="removeOption(${questionIndex}, 0)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="option-item input-group mb-2" data-option-index="1">
                                <input type="text" class="form-control" 
                                       name="preliminary_questions[${questionIndex}][options][]" 
                                       value="" 
                                       placeholder="Option 2">
                                <button type="button" class="btn btn-outline-danger" onclick="removeOption(${questionIndex}, 1)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOption(${questionIndex})">
                            <i class="fas fa-plus me-1"></i>Add Option
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    questionsList.insertAdjacentHTML('beforeend', newQuestionHTML);
    questionIndex++;
    updateQuestionNumbers();
}

function removeQuestion(index) {
    if (confirm('Are you sure you want to remove this question?')) {
        const questionItem = document.querySelector(`.question-item[data-index="${index}"]`);
        questionItem.remove();
        updateQuestionNumbers();
    }
}

function toggleQuestionOptions(questionIndex, type) {
    const optionsContainer = document.getElementById(`options_container_${questionIndex}`);
    if (type === 'multiple_choice') {
        optionsContainer.style.display = 'block';
    } else {
        optionsContainer.style.display = 'none';
    }
}

function addOption(questionIndex) {
    const optionsList = document.getElementById(`options_list_${questionIndex}`);
    const currentOptions = optionsList.querySelectorAll('.option-item');
    const newOptionIndex = currentOptions.length;
    
    const newOptionHTML = `
        <div class="option-item input-group mb-2" data-option-index="${newOptionIndex}">
            <input type="text" class="form-control" 
                   name="preliminary_questions[${questionIndex}][options][]" 
                   value="" 
                   placeholder="Option ${newOptionIndex + 1}">
            <button type="button" class="btn btn-outline-danger" onclick="removeOption(${questionIndex}, ${newOptionIndex})">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    optionsList.insertAdjacentHTML('beforeend', newOptionHTML);
}

function removeOption(questionIndex, optionIndex) {
    const optionsList = document.getElementById(`options_list_${questionIndex}`);
    const optionItems = optionsList.querySelectorAll('.option-item');
    
    if (optionItems.length > 2) { // Ensure at least 2 options remain
        const optionToRemove = optionsList.querySelector(`[data-option-index="${optionIndex}"]`);
        if (optionToRemove) {
            optionToRemove.remove();
        }
    } else {
        alert('Multiple choice questions must have at least 2 options.');
    }
}

function updateQuestionNumbers() {
    const questionItems = document.querySelectorAll('.question-item');
    questionItems.forEach((item, index) => {
        const questionNumber = item.querySelector('h6');
        if (questionNumber) {
            questionNumber.textContent = `Question ${index + 1}`;
        }
        
        // Update placeholders for options
        const optionInputs = item.querySelectorAll('.option-item input[type="text"]');
        optionInputs.forEach((input, optionIndex) => {
            input.placeholder = `Option ${optionIndex + 1}`;
        });
    });
}

// Initialize question type toggles for existing questions
document.addEventListener('DOMContentLoaded', function() {
    const typeSelects = document.querySelectorAll('.question-type-select');
    typeSelects.forEach(select => {
        const questionIndex = select.id.split('_').pop();
        toggleQuestionOptions(questionIndex, select.value);
    });
});
</script>
