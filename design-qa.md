# B2B video hero — design QA

## Reference and implementation

- Reference: Langdock desktop and mobile hero screenshots supplied in chat on 2026-09-29.
- Local implementation: `http://vizaje-nica.local/ro/b2b`.
- Desktop visual check: in-app browser, approximately 768 px wide viewport. The intro, framed autoplay preview, centered watch pill, and scroll cue are visible.
- Interaction check: the watch button opens the native video dialog with player controls; the close button returns the same video element to the muted inline preview.

## Responsive and accessibility notes

- Mobile styles stack and left-align the intro above the video, use a compact 4:3 preview, and make the player dialog fill the viewport. These styles were reviewed in CSS but not visually checked at a phone-sized viewport.
- Dialog has a labelled heading, native Escape handling, focus on close, and a visible close control. Native video controls provide playback and full-screen controls where supported by the browser/device.
- Preview autoplay is muted and lazy-loaded; opening the dialog unmutes playback following the user gesture.

## Remaining verification

- Check at actual mobile widths and on an iOS/Android browser, especially native full-screen behavior and unmuted playback policy.
- No browser console capture or same-viewport side-by-side reference comparison was available in this pass; do not treat this as a full visual sign-off.
