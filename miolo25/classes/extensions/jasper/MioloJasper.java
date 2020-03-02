package br.ufjf.miolo;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Date;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Properties;
import java.util.Set;
import java.util.StringTokenizer;
import java.util.UUID;
import java.util.Map.Entry;

import net.sf.jasperreports.engine.JRException;
import net.sf.jasperreports.engine.JasperManager;
import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.JasperFillManager;
import net.sf.jasperreports.engine.JasperExportManager;
import net.sf.jasperreports.engine.JasperReport;

public class MioloJasper {
	@SuppressWarnings({"unchecked","deprecation"})
	public static void main(String[] args) throws FileNotFoundException {
		Connection conn = null;
		try {
			System.out.println("Iniciado...");
			
			Properties prop = new Properties();
			try
			{
			    prop.load(new FileInputStream("miolojasper.properties"));
			}
			catch (FileNotFoundException e)
			{
			    e.printStackTrace();
			}
			catch (IOException e)
			{
			    e.printStackTrace();
			}
			
			HashMap <String, String> params = new HashMap<String, String>();
			String cmdParam = args[0];
			System.out.println(cmdParam);
			StringTokenizer st = new StringTokenizer(cmdParam,"&");
			StringTokenizer sti;
			while(st.hasMoreTokens()){
				sti = new StringTokenizer(st.nextToken(),"=");
				params.put(sti.nextToken(), sti.nextToken());
			}
			
			String fileName = params.get("relatorio");
            fileName = fileName.replace('\\', '/');

			String fileOut = params.get("fileout");
            fileOut = fileOut.replace('\\', '/');

            System.out.println(fileName);
			System.out.println(fileOut);
			System.out.println("conn_driver:" + prop.getProperty("conn_driver"));
			System.out.println("conn_db:" + prop.getProperty("conn_db"));
			System.out.println("conn_user:" + prop.getProperty("conn_user"));
			System.out.println("conn_pass:" + prop.getProperty("conn_pass"));
			
			
			Class.forName(prop.getProperty("conn_driver"));
			conn = DriverManager.getConnection(prop.getProperty("conn_db"),prop.getProperty("conn_user"),prop.getProperty("conn_pass"));
			System.out.println("Banco conectado.");

			HashMap <String, Object> parametros = new HashMap<String, Object>();
			
            String nomeParExt;
            Set paramsSet = params.entrySet();
            Iterator i = paramsSet.iterator();
            while (i.hasNext()) {
                nomeParExt = (String)((Entry)i.next()).getKey();
                if ((!nomeParExt.equalsIgnoreCase("relatorio")) && (!nomeParExt.equalsIgnoreCase("bd"))){
                    String prefixo = nomeParExt.substring(0,4);
                    String nomePar = nomeParExt.substring(4);
                    String param = params.get(nomeParExt);
                    System.out.println(param);
                    if (prefixo.equalsIgnoreCase("int_")) {
                        parametros.put(nomePar, new Integer(param));
                    } else if (prefixo.equalsIgnoreCase("dbl_")) {
                        parametros.put(nomePar, new Double(param));
                    } else if (prefixo.equalsIgnoreCase("tsp_")) {
                        String dataStr = param.substring(2,4) + "/" + param.substring(0,2) + "/" + param.substring(4,8);
                        parametros.put(nomePar, new Date(dataStr));
                    } else if (prefixo.equalsIgnoreCase("boo_")) {
                        if (param.equalsIgnoreCase("1")) {
                            parametros.put(nomePar, new Boolean(true));
                        }
                        else {
                            parametros.put(nomePar, new Boolean(false));
                        }
                    } else if (prefixo.equalsIgnoreCase("str_")) {
                        parametros.put(nomePar, new String(param));
                    } else {
                        parametros.put(nomeParExt, new String(param));
                    }
                }
                
            }
			System.out.println("Parâmetros preparados.");

			String jr = fileName;
			System.out.println(jr);
			/*
			JasperPrint jp = JasperFillManager.fillReport(jr,parametros,conn);
			System.out.println("Relatório preenchido.");
			
			
			JasperExportManager.exportReportToPdfFile(jp,fileOut);
			*/
            FileInputStream stream = new FileInputStream(fileName);
            JasperReport relatorio = JasperManager.loadReport(stream);
			JasperPrint impressao = JasperManager.fillReport(relatorio, parametros, conn);
            JasperManager.printReportToPdfFile(impressao, fileOut);

			System.out.println("Relatório exportado p/ PDF.");
			
		} catch (SQLException ex) {
            ex.printStackTrace();
		} catch (ClassNotFoundException ex) {
            ex.printStackTrace();
		} catch (JRException e) {
            e.printStackTrace();
		} catch (Exception e) {
            e.printStackTrace();
		} finally {
            try {
                if (!conn.isClosed()) { conn.close(); }
                System.out.println("Finalizado!");
            } catch (SQLException ex) {}
		}
	}
}