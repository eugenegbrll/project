document.querySelectorAll('.animal-option').forEach(option => {
    option.addEventListener('click', function () {
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;

        document.querySelectorAll('.animal-option').forEach(opt => {
            opt.style.borderColor = '#ddd';
            opt.style.backgroundColor = 'white';
        });

        this.style.borderColor = '#4CAF50';
        this.style.backgroundColor = '#e8f5e9';
    });
});
