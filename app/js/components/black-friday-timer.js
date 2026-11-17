(() => {
	'use strict'

	const pad = value => String(value).padStart(2, '0')

	const updateTimers = () => {
		document.querySelectorAll('[data-black-friday-timer]').forEach(timer => {
			const endAt = Number(timer.dataset.endsAt) * 1000
			const clock = timer.querySelector('[data-black-friday-countdown]')
			if (!clock || !Number.isFinite(endAt)) {
				timer.hidden = true
				return
			}

			const remaining = Math.max(0, Math.floor((endAt - Date.now()) / 1000))
			if (remaining <= 0) {
				timer.hidden = true
				return
			}

			const days = Math.floor(remaining / 86400)
			const hours = Math.floor((remaining % 86400) / 3600)
			const minutes = Math.floor((remaining % 3600) / 60)
			const seconds = remaining % 60
			const clockText = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`
			clock.textContent = days > 0
				? `${days}${timer.dataset.dayLabel || 'd'} ${clockText}`
				: clockText
		})
	}

	updateTimers()
	window.setInterval(updateTimers, 1000)
})()
