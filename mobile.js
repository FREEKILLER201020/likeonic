var web_root = "/"
if (isMobileDevice()) {
  var iOS = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);
  var width = document.documentElement.clientWidth;
  var height = document.documentElement.clientHeight;
  // if(width<=1045){
  if (height > width) {
    if (iOS) {
      document.write("<link rel=\"stylesheet\" href=\"https://freekiller201020.github.io/likeonic/style2_mobile_ios.css\">");
    } else {
      document.write("<link rel=\"stylesheet\" href=\"https://freekiller201020.github.io/likeonic/style2_mobile.css\">");
    }
    // }
  }

  // if(width<=1045){
  if (height <= width) {
    if (height <= 600) {
      alert("Please rotate your phone to portrait mode");
      window.stop();
    } else {
      console.log("tablet time!");
      document.write("<link rel=\"stylesheet\" href=\"https://freekiller201020.github.io/likeonic/style2_tablet.css\">");
    }
  }
  // }
} else {
  document.write("<link rel=\"stylesheet\" href=\"https://freekiller201020.github.io/likeonic/style2.css\">");
}