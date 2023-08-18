// Define the YouTube player variable
var player;

// This function is called when the API is ready
function onYouTubeIframeAPIReady() {
    player = new YT.Player('player', {
        height: '315',
        width: '560',
        videoId: 'DX0VLsA3how',
        events: {
            'onStateChange': onPlayerStateChange
        }
    });
}

// This function is called when the player's state changes
function onPlayerStateChange(event) {
    if (event.data == YT.PlayerState.PLAYING) {
        // Expand the overlay to cover the entire video
        document.getElementById('video-overlay').style.display = 'block';
    }
}

// Mute button functionality
document.getElementById('mute-button').addEventListener('click', function() {
    player.mute();
});

// Stop button functionality
document.getElementById('stop-button').addEventListener('click', function() {
    player.stopVideo();
});
