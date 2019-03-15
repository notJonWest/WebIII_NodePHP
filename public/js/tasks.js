$(() =>
{
    let $status = $("#status");
    $status.on("change", makeTable);
    let updater = setInterval(()=>makeTable(parseInt($status.val())), 3000);
    makeTable(parseInt($status.val()));
});

let makeTable = (status) =>
{
    if(status.target !== undefined)
        status = parseInt($(status.target).val());

    console.log(status);
    $.get(`getTaskInfo.php?status=${status}`)
        .done(data =>
            {
                if (data.length !== 0)
                {
                    $("#tasks").html(`
                         <tr id="headerRow">
                            <th>Title</th>
                            <th>Updated</th>
                        </tr>`);
                    if (status === 0)
                        $("#headerRow").append("<th>Status</th>");
                    for (let task of data)
                    {
                        let statusCell = "";
                        if (status === 0)
                            statusCell = `<td><label for="task${task.id}">${getTaskStatus(task.status)}</label></td>`;
                        $("#tasks").append(`<tr class="submitable">
                                                <td>
                                                    <label for="task${task.id}">${task.title}</label>
                                                    <input type="submit" name="id" value="${task.id}" id="task${task.id}" class="hide"/>    
                                                </td>
                                                <td><label for="task${task.id}">${task.dateModified}</label></td>
                                                ${statusCell}
                                            </tr>`);
                    }
                }
                else
                {
                    $("#tasks").html(`<tr><th>There are currently no tasks under "${getTaskStatus(status)}".</th></tr>`);
                }
            }
        );
};

let getTaskStatus = (num) =>
{
    let str = "";
    switch (parseInt(num))
    {
        case 0:
            str = "All";
            break;
        case 1:
            str = "To Do";
            break;
        case 2:
            str = "In Development";
            break;
        case 3:
            str = "Testing";
            break;
        case 4:
            str = "Complete";
            break;
        default:
            str = "Unknown";
    }
    return str;
};