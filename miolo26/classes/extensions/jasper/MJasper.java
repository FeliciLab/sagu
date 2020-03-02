
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

import java.util.HashMap;
import java.util.Properties;
import java.util.StringTokenizer;
import java.util.Set;
//import java.util.UUID;
import java.util.Map.Entry;
import java.util.Iterator;
import java.util.Date;

import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.File;
import java.io.IOException;
import java.io.FileNotFoundException;

import net.sf.jasperreports.engine.JRException;
import net.sf.jasperreports.engine.JasperFillManager;
import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.JasperExportManager;
import net.sf.jasperreports.engine.export.JRCsvExporter;
import net.sf.jasperreports.engine.export.JRHtmlExporter;
import net.sf.jasperreports.engine.export.JRHtmlExporterParameter;
import net.sf.jasperreports.engine.export.JRRtfExporter;
import net.sf.jasperreports.engine.export.JRPdfExporter;
import net.sf.jasperreports.engine.export.oasis.JROdtExporter;
import net.sf.jasperreports.engine.export.JExcelApiExporter;
import net.sf.jasperreports.engine.JRExporterParameter;

// Added for showing SQL query on error handling
import net.sf.jasperreports.engine.util.JRLoader;
import net.sf.jasperreports.engine.JasperReport;

// Used to JRXML compiling
import net.sf.jasperreports.engine.design.JasperDesign;
import net.sf.jasperreports.engine.xml.JRXmlLoader;
import net.sf.jasperreports.engine.JasperCompileManager;

import net.sf.jasperreports.engine.export.FontKey;
import net.sf.jasperreports.engine.export.PdfFont;

import com.jaspersoft.jrx.export.JRTxtExporter;
import com.jaspersoft.jrx.export.JRTxtExporterParameter;

import jxl.write.biff.RowsExceededException;

public class MJasper {

    public static void main(String[] args) throws Exception {

        Connection conn = null;

        try {
            //Carregando configurações da Conexão com o banco de dados
            Properties prop = new Properties();

            HashMap<String, String> params = new HashMap<String, String>();

            String path = args[0];
            String cmdParam = args[1];
            String dbUser = args[2];
            String dbPass = args[3];
            String jdbcDriver = args[4];
            String jdbcDb = args[5];

            StringTokenizer st = new StringTokenizer(cmdParam, "&");
            StringTokenizer sti;
            while (st.hasMoreTokens()) {
                sti = new StringTokenizer(st.nextToken(), "~");
                params.put(sti.nextToken(), sti.nextToken());
            }

            /*String bd = params.get("bd");
            prop.load(new FileInputStream(path + "/" + bd + ".properties"));*/

            String fileName = params.get("relatorio");
            fileName = fileName.replace('\\', '/');

            String fileOut = params.get("fileout");
            fileOut = fileOut.replace('\\', '/');

            String fileType = params.get("filetype");

            /*Class.forName(prop.getProperty("conn_driver"));
            conn = DriverManager.getConnection(prop.getProperty("conn_db"),prop.getProperty("conn_user"),prop.getProperty("conn_pass"));*/

            if ( jdbcDriver != "" )
            {
                Class.forName(jdbcDriver);
                conn = DriverManager.getConnection(jdbcDb, dbUser, dbPass);
            }
            else
            {
                conn = null;
            }

            HashMap<String, Object> parametros = new HashMap<String, Object>();

            String nomeParExt;
            Set paramsSet = params.entrySet();
            Iterator i = paramsSet.iterator();
            while (i.hasNext()) {
                nomeParExt = (String) ((Entry) i.next()).getKey();
                if ((!nomeParExt.equalsIgnoreCase("relatorio")) && (!nomeParExt.equalsIgnoreCase("bd"))) {
                    String prefixo = nomeParExt.substring(0, 4);
                    String nomePar = nomeParExt.substring(4);
                    String param = params.get(nomeParExt);
                    if (prefixo.equalsIgnoreCase("int_")) {
                        parametros.put(nomePar, new Integer(param));
                    } else if (prefixo.equalsIgnoreCase("dbl_")) {
                        parametros.put(nomePar, new Double(param));
                    } else if (prefixo.equalsIgnoreCase("tsp_")) {
                        String dataStr = param.substring(2, 4) + "/" + param.substring(0, 2) + "/" + param.substring(4, 8);
                        parametros.put(nomePar, new Date(dataStr));
                    } else if (prefixo.equalsIgnoreCase("boo_")) {
                        if (param.equalsIgnoreCase("1")) {
                            parametros.put(nomePar, new Boolean(true));
                        } else {
                            parametros.put(nomePar, new Boolean(false));
                        }
                    } else if (prefixo.equalsIgnoreCase("str_")) {
                        parametros.put(nomePar, new String(param));
                    } else {
                        parametros.put(nomeParExt, new String(param));
                    }
                }

            }

            JasperPrint impressao;
            JasperReport report;

            int dot = fileName.lastIndexOf('.');

            if ( fileName.substring(dot + 1).equals("jrxml") )
            {
                JasperDesign design = JRXmlLoader.load(fileName);
                report = JasperCompileManager.compileReport(design);
            }
            else
            {
                report = (JasperReport) JRLoader.loadObjectFromFile(fileName);
            }

            try
            {
                if ( conn != null )
                {
                    impressao = JasperFillManager.fillReport(report, parametros, conn);
                }
                else
                {
                    impressao = JasperFillManager.fillReport(report, parametros);
                }
            }
            catch ( JRException e )
            {
                // Error message and cause
                System.err.println(e.getMessage());
                System.err.println(e.getCause().getMessage().concat("\n"));

                // Stack trace
                e.printStackTrace();
                System.err.println();

                // SQL clause
                System.err.println(report.getQuery().getText());

                throw e;
            }

            if (impressao.getPages().isEmpty()) {
                System.out.println("empty");
            } else {
                if (fileType.toUpperCase().equals("TXT")) {
                    JRTxtExporter txtExporter = new JRTxtExporter();
                    txtExporter.setParameter(JRTxtExporterParameter.JASPER_PRINT, impressao);
                    txtExporter.setParameter(JRTxtExporterParameter.OUTPUT_FILE_NAME, fileOut);
                    if (params.get("TXT_PAGE_ROWS") != null) {
                        txtExporter.setParameter(JRTxtExporterParameter.PAGE_ROWS, params.get("TXT_PAGE_ROWS"));
                    }
                    if (params.get("TXT_PAGE_COLUMNS") != null) {
                        txtExporter.setParameter(JRTxtExporterParameter.PAGE_COLUMNS, params.get("TXT_PAGE_COLUMNS"));
                    }
                    if (params.get("TXT_ADD_FORM_FEED") != null) {
                        txtExporter.setParameter(JRTxtExporterParameter.ADD_FORM_FEED, params.get("TXT_ADD_FORM_FEED"));
                    }
                    txtExporter.exportReport();
                } else if (fileType.toUpperCase().equals("CSV")) {
                    JRCsvExporter csvExporter = new JRCsvExporter();
                    csvExporter.setParameter(JRExporterParameter.JASPER_PRINT, impressao);
                    csvExporter.setParameter(JRExporterParameter.OUTPUT_FILE_NAME, fileOut);
                    csvExporter.exportReport();
                } else if (fileType.toUpperCase().equals("HTML")) {
                    JRHtmlExporter htmlExporter = new JRHtmlExporter();
                    htmlExporter.setParameter(JRExporterParameter.JASPER_PRINT, impressao);
                    htmlExporter.setParameter(JRExporterParameter.OUTPUT_FILE_NAME, fileOut);
                    htmlExporter.setParameter(JRHtmlExporterParameter.IS_USING_IMAGES_TO_ALIGN, false);
                    htmlExporter.exportReport();
                } else if (fileType.toUpperCase().equals("ODT")) {
                    JROdtExporter odtExporter = new JROdtExporter();
                    odtExporter.setParameter(JRExporterParameter.JASPER_PRINT, impressao);
                    odtExporter.setParameter(JRExporterParameter.OUTPUT_FILE_NAME, fileOut);
                    odtExporter.exportReport();
                } else if (fileType.toUpperCase().equals("XLS")) {
                    JExcelApiExporter xlsExporter = new JExcelApiExporter();
                    xlsExporter.setParameter(JRExporterParameter.JASPER_PRINT, impressao);
                    xlsExporter.setParameter(JRExporterParameter.OUTPUT_FILE_NAME, fileOut);
                    xlsExporter.exportReport();
                } else if (fileType.toUpperCase().equals("RTF")) {
                    JRRtfExporter rtfExporter = new JRRtfExporter();
                    rtfExporter.setParameter(JRExporterParameter.JASPER_PRINT, impressao);
                    rtfExporter.setParameter(JRExporterParameter.OUTPUT_FILE_NAME, fileOut);
                    rtfExporter.exportReport();
                } else {
                    JRPdfExporter exporter = new JRPdfExporter();

                    FontKey keyArial = new FontKey("Arial", false, false);
                    PdfFont fontArial = new PdfFont("Helvetica","Cp1252",false);
                    FontKey keyArialBold = new FontKey("Arial", true, false);
                    PdfFont fontArialBold = new PdfFont("Helvetica-Bold","Cp1252",false);

                    FontKey keySans = new FontKey("SansSerif", false, false);
                    PdfFont fontSans = new PdfFont("Helvetica","Cp1252",false);
                    FontKey keySansBold = new FontKey("SansSerif", true, false);
                    PdfFont fontSansBold = new PdfFont("Helvetica-Bold","Cp1252",false);

                    FontKey keyTimes = new FontKey("Times New Roman", false, false);
                    PdfFont fontTimes = new PdfFont("Times-Roman","Cp1252",false);
                    FontKey keyTimesBold = new FontKey("Times New Roman", true, false);
                    PdfFont fontTimesBold = new PdfFont("Times-Bold","Cp1252",false);

                    java.util.Map fontMap = new HashMap();
                    fontMap.put(keyArial,fontArial);
                    fontMap.put(keyArialBold,fontArialBold);
                    fontMap.put(keySans,fontSans);
                    fontMap.put(keySansBold,fontSansBold);
                    fontMap.put(keyTimes,fontTimes);
                    fontMap.put(keyTimesBold,fontTimesBold);

                    exporter.setParameter(JRExporterParameter.FONT_MAP, fontMap);
                    exporter.setParameter(JRExporterParameter.JASPER_PRINT, impressao);
                    exporter.setParameter(JRExporterParameter.OUTPUT_FILE_NAME, fileOut);
                    exporter.exportReport();
                }

                System.out.println("end");
            }
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
    }
}
