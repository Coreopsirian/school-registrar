document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btn-export').addEventListener('click', function () {
        const table = document.getElementById('students-table');
        const wb = XLSX.utils.table_to_book(table, { sheet: "Students" });
        XLSX.writeFile(wb, 'students_report.xlsx');
    });
});