const http = require("http");
const url = require("url");
const fs = require("fs");
const path = require("path");
const qs = require("querystring");
const request = require("request");
const mst = require("mustache");
const PORT = 7546;

const DEFAULT_FILE = "index.html";
const ROOTDIR = "../public";
const ERRDIR = "../private/errorpages";
const PHPROOT = "http://csdev.cegep-heritage.qc.ca/students/1723424/jwest_C31A04/jwest_C31A04PHP/";

const extToMIME = {
    ".css": "text/css",
    ".html": "text/html",
    ".htm": "text/html",
    ".js": "application/javascript",
    ".json": "application/json",
    ".jpg": "image/jpeg",
    ".jpeg": "image/jpeg",
    ".gif": "image/gif",
    ".pdf": "application/pdf",
    ".png": "image/png",
    ".svg": "image/svg+xml",
    ".txt": "text/plain",
    ".ico": "image/x-icon",
    ".xml": "text/xml",
};

const statusMsgs = {
    200: "OK",
    400: "Bad Request", //Expecting certain extension
    404: "Not Found",
    406: "Not Acceptable",
    415: "Unsupported Media Type",
    416: "Range not satisfiable", //No matching record found
    500: "Internal Server Error",
    520: "Writing Error",
};

http.createServer((req, res) =>
{
    let statusCode = 200;
    let content = "";
    let contentType = extToMIME[".txt"];

    let urlObj = url.parse(req.url, true);
    let filePath = path.parse(urlObj.pathname);
    let fullDir = path.join(ROOTDIR, filePath.dir);

    let newUrlPath = (...newUrl) =>
    {
        urlObj = url.parse(path.join(...newUrl), true);
        filePath = path.parse(urlObj.pathname);
        fullDir = path.join(filePath.dir);
    };

    /**
     * Sends default response and writes to the log file
     * @param sCode: status code
     * @param cType: content-type
     * @param cont: response content
     * @param _res: response object
     */
    let finishResponse = (sCode = statusCode, cType = contentType, cont = content, _res = res) =>
    {
        //Make variables consistent
        statusCode = sCode;
        contentType = cType;
        content = cont;
        sendResponse(_res, sCode, cont, cType);
    };

    if (filePath.ext === ".php")
    {
        let options =
        {
            "url": PHPROOT + urlObj.path,
            "method": req.method
        };
        let data = "";
        req.on("data", chunk=>data += chunk.toString());
        req.on("end", ()=>
        {
            options["form"] = qs.parse(data);
            if (filePath.base === "getTaskDetail.php")
                request(options,
                    (err, resres, body) =>
                    {
                        if (err)
                        {
                            console.log(err);
                            finishResponse(500);
                        }
                        else if (body === "")
                        {
                            finishResponse(416);
                        }
                        else
                        {
                            let readableStream = fs.createReadStream(`./templates/${filePath.name}.mst`);
                            let template = "";
                            readableStream.setEncoding("utf8");
                            readableStream.on("data", chunk => {
                                template += chunk;
                            });
                            readableStream.on("end", () => {
                                let taskObj = JSON.parse(body);
                                finishResponse(200,
                                    extToMIME[".html"],
                                    mst.to_html(template, taskObj)
                                );
                            });
                            readableStream.on("error", mstErr =>
                            {
                                console.log(mstErr);
                                finishResponse(500);
                            });
                        }

                    });
            else if (filePath.base === "getTaskInfo.php")
                request(options,
                    (err, resres, body) =>
                    {
                        if (err)
                            finishResponse(500);
                        else if (body === "")
                            finishResponse(416);
                        else
                            finishResponse(200, resres.headers["content-type"], body);
                    });
            else
                finishResponse(404);
        });
    }
    else
    {
        if (extToMIME[filePath.ext] === undefined)
        {
            if (filePath.ext === '')
            {
                let srchInfo = urlObj.pathname.split("/");
                srchInfo.shift(); //remove the empty string
                if (fs.existsSync(path.join(fullDir, filePath.base, DEFAULT_FILE)))
                    newUrlPath(fullDir, filePath.base, DEFAULT_FILE);
                else
                    statusCode = 404;
            } //if ext === ''
            else
                statusCode = 415;
        } //if extToMIME[ext] === undefined
        fs.readFile(path.join(fullDir, filePath.base), (err, data) =>
        {
            if (statusCode === 200)
            {
                contentType = extToMIME[filePath.ext];
                content = data;
            }
            finishResponse();
        });
    }

}).listen(PORT);

let sendResponse = (res, sCode, cont, cType) =>
{
    console.log(sCode);
    if (sCode !== 200)
    {
        let errorCont = cont;
        fs.readFile(path.join(ERRDIR, `${sCode}.html`), (err, data) =>
        {
            if (err) {
                cType = extToMIME[".txt"];
                cont = `${sCode}: ${statusMsgs[sCode]}`;
            }
            else {
                cType = extToMIME[".html"];
                cont = data;
            }

            res.writeHead(sCode, {
                "Content-Type": cType,
                "Failed-Content": errorCont,
                "Accept-Ranges": "none"
            });
            res.end(cont);
        });
    }
    else
    {
        res.writeHead(sCode, {
            "Content-Type": cType
        });
        res.end(cont);
    }
}; //sendResponse