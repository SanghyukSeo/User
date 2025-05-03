
function submitEmergency(type, priority) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'submit_report.php';
  
    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = type;
    form.appendChild(typeInput);
  
    const priorityInput = document.createElement('input');
    priorityInput.type = 'hidden';
    priorityInput.name = 'priority';
    priorityInput.value = priority;
    form.appendChild(priorityInput);
  
    document.body.appendChild(form);
    form.submit();
  }
