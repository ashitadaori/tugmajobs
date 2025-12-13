// Paste this code in browser console when viewing FINAL_FRAMEWORK.html

// Function to capture the flowchart as PNG
function captureFlowchartAsImage() {
    // Use html2canvas library
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
    document.head.appendChild(script);
    
    script.onload = function() {
        const element = document.querySelector('.container');
        
        html2canvas(element, {
            backgroundColor: null,
            scale: 2, // Higher quality
            useCORS: true,
            allowTaint: true,
            width: 1200,
            height: 900
        }).then(function(canvas) {
            // Create download link
            const link = document.createElement('a');
            link.download = 'job-portal-framework.png';
            link.href = canvas.toDataURL('image/png');
            
            // Trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            console.log('Framework image downloaded successfully!');
        }).catch(function(error) {
            console.error('Error capturing image:', error);
        });
    };
}

// Run the function
captureFlowchartAsImage();
