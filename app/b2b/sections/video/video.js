function loadVideo(video) {
	if (video.dataset.videoLoaded === "true") return;

	const source = video.querySelector("source[data-src]");
	if (!source) return;

	source.src = source.dataset.src;
	delete source.dataset.src;
	video.dataset.videoLoaded = "true";
	video.load();
	video.muted = true;
	video.play().catch(() => {
		// Autoplay may be blocked; keep the poster visible as a graceful fallback.
	});
}

export function initVideo() {
	const video = document.querySelector("[data-lazy-video]");
	if (!video) return;

	if (!("IntersectionObserver" in window)) {
		loadVideo(video);
		return;
	}

	const observer = new IntersectionObserver(
		(entries) => {
			if (!entries.some((entry) => entry.isIntersecting)) return;

			loadVideo(video);
			observer.disconnect();
		},
		{ rootMargin: "400px 0px" },
	);

	observer.observe(video);
}
