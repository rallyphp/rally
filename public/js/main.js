$(function() {
  var msg;
  var voice;
  var text;

  window.speechSynthesis.onvoiceschanged = function() {
console.log('voices changed');
    voice = window.speechSynthesis.getVoices()[1];

    for (var a = 9; a < 10; a++) {
      for (var b = 4; b < 10; b++) {
        msg = new SpeechSynthesisUtterance();
console.log('setting voice for msg');
        msg.voice = voice;
        msg.text = 'Brandi scored a point.  The score is now ' + a + ' - ' + b;

        console.log(msg.text);
        window.speechSynthesis.speak(msg);
console.log('spoke it');
        break;
      }
      break;
    }
  };

});
