// Sample order data (replace with data from your backend)
const orders = [
    { id: 1, name: "John Doe", product: "Cat", quantity: 1, price: 1200, date: "2025-03-01" },
    { id: 2, name: "Jane Smith", product: "Dog", quantity: 2, price: 800, date: "2025-03-02" },
    { id: 3, name: "Alice Johnson", product: "Bird", quantity: 1, price: 500, date: "2025-03-03" }
];

// Function to display orders in the table
function displayOrders() {
    const tableBody = document.getElementById("orderTableBody");

    // Clear existing rows
    tableBody.innerHTML = "";

    // Add rows for each order
    orders.forEach(order => {
        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${order.id}</td>
            <td>${order.name}</td>
            <td>${order.product}</td>
            <td>${order.quantity}</td>
            <td>$${order.price}</td>
            <td>${order.date}</td>
        `;

        tableBody.appendChild(row);
    });
}

// Load orders when the page loads
window.onload = displayOrders;