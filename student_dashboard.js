let patCount = 0;
let idleTimer;
let lastPatTime = Date.now();

const petEmoji = document.getElementById('petEmoji');
const patCounter = document.getElementById('patCounter');
const speechBubble = document.getElementById('speechBubble');
const petMood = document.getElementById('petMood');
const petBox = document.querySelector('.pet-box');

const phrases = [
    '❤️ I love you!',
    '😊 Yay!',
    '✨ That feels nice!',
    '🥰 More pats!',
    '💕 Thank you!',
    '🎉 Woohoo!'
];

const moods = [
    '😊 Happy',
    '🥰 Loved',
    '😍 Excited',
    '✨ Joyful',
    '💖 Blessed'
];

function playPetSound() {
    const petEmoji = document.getElementById("petEmoji");
    const soundFile = petEmoji.dataset.sound;

    const audio = new Audio(soundFile);
    audio.play();
}

function patPet() {
    playPetSound();
    patCount++;
    patCounter.textContent = patCount;
    lastPatTime = Date.now();

    petEmoji.classList.remove('squish', 'happy', 'wiggle', 'idle-blink', 'idle-bounce');

    const animations = ['squish', 'happy', 'wiggle'];
    const randomAnim = animations[Math.floor(Math.random() * animations.length)];
    petEmoji.classList.add(randomAnim);

    const randomPhrase = phrases[Math.floor(Math.random() * phrases.length)];
    speechBubble.textContent = randomPhrase;
    speechBubble.classList.add('show');

    const randomMood = moods[Math.floor(Math.random() * moods.length)];
    petMood.textContent = randomMood;

    createHeart();

    setTimeout(() => { speechBubble.classList.remove('show'); }, 2000);
    setTimeout(() => { petEmoji.classList.remove(randomAnim); }, 500);

    resetIdleTimer();
}

function createHeart() {
    const heart = document.createElement('div');
    heart.className = 'heart';
    heart.textContent = '❤️';

    const randomX = Math.random() * 100 - 50;
    heart.style.left = `calc(50% + ${randomX}px)`;
    heart.style.top = '50%';

    petBox.appendChild(heart);

    setTimeout(() => { heart.remove(); }, 1500);
}

function startIdleAnimation() {
    const idleAnimations = ['idle-blink', 'idle-bounce'];
    const randomIdle = idleAnimations[Math.floor(Math.random() * idleAnimations.length)];

    petEmoji.classList.add(randomIdle);

    setTimeout(() => {
        petEmoji.classList.remove(randomIdle);
    }, 3000);
}

function resetIdleTimer() {
    clearInterval(idleTimer);
    idleTimer = setInterval(() => {
        const timeSinceLastPat = Date.now() - lastPatTime;
        if (timeSinceLastPat > 8000) startIdleAnimation();
    }, 8000);
}

function toggleTodo(id) {
    fetch("toggle_todo.php?id=" + id)
        .then(response => response.text())
        .then(result => {
            let li = document.getElementById("todo-" + id);
            if (result === "1") {
                li.style.textDecoration = "line-through";
                li.style.color = "#999";
            } else {
                li.style.textDecoration = "none";
                li.style.color = "#000";
            }
        });
}


resetIdleTimer();
setTimeout(startIdleAnimation, 3000);
