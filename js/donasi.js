const manualInput = document.getElementById('manualNominal')
const nominalFix = document.getElementById('nominalFix')

// Format angka ke Rupiah
function formatRupiah (angka) {
  return new Intl.NumberFormat('id-ID').format(angka)
}

// Hitung dampak real-time (1 Pohon = 10.000)
function calculateImpact (amount) {
  let trees = Math.floor(amount / 10000)
  if (trees < 1) trees = 1
  let oxygen = trees * 100 // Asumsi 1 pohon = 100 liter oksigen

  document.getElementById('impactTree').innerText = trees
  document.getElementById('impactOxygen').innerText = formatRupiah(oxygen)
  document.getElementById('impactNominal').innerText = formatRupiah(amount)
}

// Set nominal dari Badge
function setNominal (amount) {
  nominalFix.value = amount
  manualInput.value = 'Rp ' + formatRupiah(amount)
  calculateImpact(amount)

  // Update UI Active State
  document
    .querySelectorAll('.badge-nominal')
    .forEach(el => el.classList.remove('active'))
  // Logic tambahan untuk highlight badge yang sesuai (opsional, perlu mapping ID)
}

// Event Listener: Klik Badge
document.querySelectorAll('.badge-nominal').forEach(badge => {
  badge.addEventListener('click', function () {
    document
      .querySelectorAll('.badge-nominal')
      .forEach(el => el.classList.remove('active'))
    this.classList.add('active')
  })
})

// Event Listener: Input Manual (Hanya Angka)
manualInput.addEventListener('keyup', function (e) {
  let rawValue = this.value.replace(/[^0-9]/g, '')
  if (rawValue === '') rawValue = '0'
  let amount = parseInt(rawValue)

  nominalFix.value = amount
  this.value = 'Rp ' + formatRupiah(amount)
  calculateImpact(amount)

  // Reset active state badge karena input manual custom
  document
    .querySelectorAll('.badge-nominal')
    .forEach(el => el.classList.remove('active'))
})
