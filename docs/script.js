function toggleCard(cardNumber) {
    const icon = document.getElementById('icon' + cardNumber);
    icon.classList.toggle('rotated');

    const allDetails = document.querySelectorAll('.card-detail');
    allDetails.forEach(detail => detail.style.display = 'none');

    const allIs = document.querySelectorAll('.cards .card-header span i');
    allIs.forEach(i => {
        if (icon.id === i.id) {
            return;
        }
        i.classList.remove('rotated');
    });

    const cardDetail = document.getElementById('card-detail' + cardNumber);
    cardDetail.style.display = 'block';
}
