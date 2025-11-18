// detector.js
let _stream = null;
let _rafId = null;

async function startDetection(
  videoId = "videoElement",
  canvasId = "canvasElement"
) {
  const video = document.getElementById(videoId);
  const canvas = document.getElementById(canvasId);

  // load models from assets/models (models is parallel to js)
  await faceapi.nets.tinyFaceDetector.loadFromUri("/assets/models/");
  await faceapi.nets.faceLandmark68TinyNet.loadFromUri("/assets/models/");

  // request camera
  _stream = await navigator.mediaDevices.getUserMedia({
    video: { width: 640, height: 480 },
    audio: false,
  });
  video.srcObject = _stream;
  video.style.display = "block";

  // ensure canvas size matches video
  video.onloadedmetadata = () => {
    video.play();
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.style.width = video.style.width || `${video.videoWidth}px`;
    canvas.style.height = video.style.height || `${video.videoHeight}px`;
    canvas.style.display = "block";
    runLoop();
  };

  function runLoop() {
    _rafId = requestAnimationFrame(async () => {
      if (video.readyState >= 2) {
        const options = new faceapi.TinyFaceDetectorOptions({
          inputSize: 224,
          scoreThreshold: 0.5,
        });
        const detections = await faceapi
          .detectAllFaces(video, options)
          .withFaceLandmarks(true);

        // draw
        const ctx = canvas.getContext("2d");
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const resized = faceapi.resizeResults(detections, {
          width: canvas.width,
          height: canvas.height,
        });

        faceapi.draw.drawDetections(canvas, resized);
        faceapi.draw.drawFaceLandmarks(canvas, resized);
      }
      runLoop();
    });
  }
}

function stopDetection(videoId = "videoElement", canvasId = "canvasElement") {
  if (_rafId) {
    cancelAnimationFrame(_rafId);
    _rafId = null;
  }
  const video = document.getElementById(videoId);
  const canvas = document.getElementById(canvasId);
  if (video && video.srcObject) {
    const tracks = video.srcObject.getTracks();
    tracks.forEach((t) => t.stop());
    video.srcObject = null;
  }
  if (canvas) {
    const ctx = canvas.getContext("2d");
    ctx && ctx.clearRect(0, 0, canvas.width, canvas.height);
    canvas.style.display = "none";
  }
  if (video) {
    video.style.display = "none";
  }
}

// expose to window
window.startDetection = startDetection;
window.stopDetection = stopDetection;
