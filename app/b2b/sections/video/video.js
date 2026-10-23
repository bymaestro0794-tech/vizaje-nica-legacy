function loadVideo(video) {
	if (video.dataset.videoLoaded !== "true") {
		const source = video.querySelector("source[data-src]");
		if (source) {
			source.src = source.dataset.src;
			delete source.dataset.src;
			video.dataset.videoLoaded = "true";
			video.load();
		}
	}

	return video.dataset.videoLoaded === "true";
}

function playMutedPreview(video) {
	video.muted = true;
	video.setAttribute("muted", "");
	video.play().catch(() => {
		// Autoplay may be blocked; keep the poster visible as a graceful fallback.
	});
}

export function initVideo(lenis) {
	const video = document.querySelector("[data-lazy-video]");
	const trigger = document.querySelector("[data-video-open]");
	const dialog = document.querySelector("[data-video-modal]");
	const mount = dialog?.querySelector("[data-video-mount]");
	const closeButton = dialog?.querySelector("[data-video-close]");
	const preview = video?.parentElement;
	if (!video || !trigger || !dialog || !mount || !closeButton || !preview) return;

	let previewTime = 0;
	let observer;

	const startPreview = () => {
		if (!loadVideo(video)) return;
		video.loop = true;
		video.controls = false;
		playMutedPreview(video);
	};

	if (!("IntersectionObserver" in window)) {
		startPreview();
	} else {
		observer = new IntersectionObserver(
			(entries) => {
				if (!entries.some((entry) => entry.isIntersecting)) return;

				startPreview();
				observer.disconnect();
			},
			{ rootMargin: "400px 0px" },
		);

		observer.observe(video);
	}

	trigger.addEventListener("click", () => {
		if (dialog.open) return;
		observer?.disconnect();
		previewTime = video.dataset.videoLoaded === "true" ? video.currentTime : 0;
		loadVideo(video);
		video.pause();
		video.loop = false;
		video.controls = true;
		video.muted = false;
		video.removeAttribute("muted");
		video.removeAttribute("aria-hidden");
		mount.append(video);
		document.documentElement.classList.add("no-scroll");
		lenis?.stop();
		dialog.showModal();
		closeButton.focus();
		try {
			video.currentTime = 0;
		} catch (error) {
			// Wait for media metadata; a new source may not be seekable yet.
		}
		video.play().catch(() => {
			// Native player controls remain available if playback needs another tap.
		});
	});

	closeButton.addEventListener("click", () => dialog.close());
	dialog.addEventListener("click", (event) => {
		if (event.target === dialog) dialog.close();
	});
	dialog.addEventListener("close", () => {
		video.pause();
		video.controls = false;
		video.loop = true;
		video.muted = true;
		video.setAttribute("muted", "");
		video.setAttribute("aria-hidden", "true");
		preview.append(video);
		if (video.readyState > 0) {
			try {
				video.currentTime = previewTime;
			} catch (error) {
				// Some browsers only allow seeking after metadata has loaded.
			}
		}
		document.documentElement.classList.remove("no-scroll");
		lenis?.start();
		trigger.focus();
		playMutedPreview(video);
	});

}
