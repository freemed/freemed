package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;

import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class PostCheckWidget extends Composite {
	protected VerticalPanel vPanel;
	protected CustomListBox payerList;
	protected TextBox tbCheckNo;
	protected TextBox tbTotalAmount;
	protected FlexTable postCheckInfoFlexTable;
	protected HashSet<String> procs;
//	protected CustomModuleWidget payerWidget;
	protected CustomTable proceduresInfoTable;
	protected CustomRequestCallback callback;
	ArrayList<String> pids;

	public PostCheckWidget(HashSet<String> p, CustomRequestCallback cb) {
		callback = cb;
		procs = p;
		vPanel = new VerticalPanel();
		vPanel.setSpacing(10);
		initWidget(vPanel);
		postCheckInfoFlexTable = new FlexTable();
		// postCheckInfoFlexTable.setWidth("100%");
//		Label payerLb = new Label("Payer");
//		payerWidget = new CustomModuleWidget(
//				"api.ClaimLog.RebillDistinctPayers");
		Label checkNumberLb = new Label("Check Number");
		tbCheckNo = new TextBox();
		Label totalAmountLb = new Label("Total Amount");
		tbTotalAmount = new TextBox();

//		postCheckInfoFlexTable.setWidget(0, 0, payerLb);
//		postCheckInfoFlexTable.setWidget(0, 1, payerWidget);
		postCheckInfoFlexTable.setWidget(1, 0, checkNumberLb);
		postCheckInfoFlexTable.setWidget(1, 1, tbCheckNo);
		postCheckInfoFlexTable.setWidget(2, 0, totalAmountLb);
		postCheckInfoFlexTable.setWidget(2, 1, tbTotalAmount);

		proceduresInfoTable = new CustomTable();
		proceduresInfoTable.setAllowSelection(false);
		proceduresInfoTable.setSize("100%", "100%");
		proceduresInfoTable.setIndexName("id");
		proceduresInfoTable.addColumn("Patient", "pt_name");
		proceduresInfoTable.addColumn("Claim", "clm");
		proceduresInfoTable.addColumn("CPT", "cpt");
		proceduresInfoTable.addColumn("Service Date", "ser_date");
		proceduresInfoTable.addColumn("Paid", "paid");
		proceduresInfoTable.addColumn("Amount Billed", "amnt_bill");
		proceduresInfoTable.addColumn("Amount Allowed", "balance");
		proceduresInfoTable.addColumn("Adustment Balance", "adj_bal");
		proceduresInfoTable.addColumn("Payment", "pay");
		proceduresInfoTable.addColumn("Copay", "copay");
		proceduresInfoTable.addColumn("Left Over", "left");
		proceduresInfoTable
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {

					@Override
					public Widget setColumn(String columnName,
							final HashMap<String, String> data) {

						final int actionRow = proceduresInfoTable
								.getActionRow();
						if (columnName.compareTo("balance") == 0) {
							int row=proceduresInfoTable.getActionRow();
							proceduresInfoTable.getFlexTable().getFlexCellFormatter().setWidth(row, 6, "10%");
							pids.add(data.get("id"));
							final TextBox tbAllowedAmount = new TextBox();
							tbAllowedAmount.setWidth("100%");
							tbAllowedAmount.setText(data.get("balance"));
							tbAllowedAmount
									.addChangeHandler(new ChangeHandler() {

										@Override
										public void onChange(ChangeEvent arg0) {
											float all_amnt = 0;
											float pay = 0;
											float copay = 0;
											if (!(tbAllowedAmount.getText()
													.equals("0") || tbAllowedAmount
													.getText().equals(""))) {
												all_amnt = Float
														.parseFloat(tbAllowedAmount
																.getText()
																.trim());
											}
											TextBox tb1 = (TextBox) proceduresInfoTable
													.getWidget(8);
											TextBox tb2 = (TextBox) proceduresInfoTable
													.getWidget(9);
											if (!(tb1.getText().equals("0") || tb1
													.getText().equals(""))) {
												pay = Float.parseFloat(tb1
														.getText().trim());
											}
											if (!(tb2.getText().equals("0") || tb2
													.getText().equals(""))) {
												copay = Float.parseFloat(tb2
														.getText().trim());
											}
											Label left = (Label) proceduresInfoTable
													.getWidget(10);
											left.setText(""
													+ (all_amnt - pay - copay));
										}

									});
							return tbAllowedAmount;
						} else if (columnName.compareTo("pay") == 0) {
							int row=proceduresInfoTable.getActionRow();
							proceduresInfoTable.getFlexTable().getFlexCellFormatter().setWidth(row, 8, "10%");
							final TextBox tbPayment = new TextBox();
							tbPayment.setWidth("100%");
							tbPayment.setText("0");
							tbPayment.addChangeHandler(new ChangeHandler() {

								@Override
								public void onChange(ChangeEvent arg0) {
									float all_amnt = 0;
									float pay = 0;
									float copay = 0;
									if (!(tbPayment.getText().equals("0") || tbPayment
											.getText().equals(""))) {
										pay = Float.parseFloat(tbPayment
												.getText().trim());
									}
									TextBox tb1 = (TextBox) proceduresInfoTable
											.getWidget(6);
									TextBox tb2 = (TextBox) proceduresInfoTable
											.getWidget(9);
									if (!(tb1.getText().equals("0") || tb1
											.getText().equals(""))) {
										all_amnt = Float.parseFloat(tb1
												.getText().trim());
									}
									if (!(tb2.getText().equals("0") || tb2
											.getText().equals(""))) {
										copay = Float.parseFloat(tb2.getText()
												.trim());
									}
									Label left = (Label) proceduresInfoTable
											.getWidget(10);
									left.setText("" + (all_amnt - pay - copay));
								}

							});
							return tbPayment;
						} else if (columnName.compareTo("copay") == 0) {
							int row=proceduresInfoTable.getActionRow();
							proceduresInfoTable.getFlexTable().getFlexCellFormatter().setWidth(row, 9, "10%");
							final TextBox tbCopay = new TextBox();
							tbCopay.setWidth("100%");
							tbCopay.setText("0");
							ArrayList params = new ArrayList();							
							tbCopay.addChangeHandler(new ChangeHandler() {

								@Override
								public void onChange(ChangeEvent arg0) {
									float all_amnt = 0;
									float pay = 0;
									float copay = 0;
									if (!(tbCopay.getText().equals("0") || tbCopay
											.getText().equals(""))) {
										copay = Float.parseFloat(tbCopay
												.getText().trim());
									}
									TextBox tb1 = (TextBox) proceduresInfoTable
											.getWidget(6);
									TextBox tb2 = (TextBox) proceduresInfoTable
											.getWidget(8);
									if (!(tb1.getText().equals("0") || tb1
											.getText().equals(""))) {
										all_amnt = Float.parseFloat(tb1
												.getText().trim());
									}
									if (!(tb2.getText().equals("0") || tb2
											.getText().equals(""))) {
										pay = Float.parseFloat(tb2.getText()
												.trim());
									}
									Label left = (Label) proceduresInfoTable
											.getWidget(10);
									left.setText("" + (all_amnt - pay - copay));
								}

							});
							params.add(data.get("pt_id"));
							params.add(data.get("id"));

							Util.callApiMethod("Ledger", "getCoveragesCopayInfo", params,
									new CustomRequestCallback() {
										@Override
										public void onError() {
										}

										@Override
										public void jsonifiedData(Object d) {
											if (data != null) {
												HashMap<String, String> result = (HashMap<String, String>) d;
												//tbAmount.setEnabled(false);
												if (result!=null) {
													tbCopay
																	.setText(result.get("copay"));
													try{
														Label lbLeft = new Label();
														float left=0;
														float copay=Float.parseFloat(result.get("copay"));
														left = Float.parseFloat(data.get("left"));														
														lbLeft.setText(""+(left-copay));
														proceduresInfoTable.getFlexTable().setWidget(actionRow, 10, lbLeft);
													}
													catch(Exception e){
														Window.alert("aaaa");
													}
															//tbAmount.setEnabled(false);								
												}
											}
										}
									}, "HashMap<String,String>");
							return tbCopay;
						} else if (columnName.compareTo("left") == 0) {
							int row=proceduresInfoTable.getActionRow();
							proceduresInfoTable.getFlexTable().getFlexCellFormatter().setWidth(row, 10, "10%");
							try{
								Label lb = (Label) proceduresInfoTable.getWidget(10);
								return lb;
							}
							catch(Exception e){
								return new Label();
							}
						}
						else if (columnName.compareTo("adj_bal") == 0) {
							int row=proceduresInfoTable.getActionRow();
							proceduresInfoTable.getFlexTable().getFlexCellFormatter().setWidth(row, 7, "10%");
							Label adjbal = new Label();
							adjbal.setText(data.get("adj_bal"));
							return adjbal;
						}
						else if (columnName.compareTo("amnt_bill") == 0) {
							int row=proceduresInfoTable.getActionRow();
							proceduresInfoTable.getFlexTable().getFlexCellFormatter().setWidth(row, 5, "10%");
							Label amntbill = new Label();
							amntbill.setText(data.get("amnt_bill"));
							return amntbill;
						}
						
						else {
							return (Widget) null;
						}

					}
				});
		HorizontalPanel actionPanel = new HorizontalPanel();
		actionPanel.setSpacing(5);
		//actionPanel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_RIGHT);
		
		CustomButton postBtn = new CustomButton("Post",AppConstants.ICON_ADD);
		postBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				prepareDataForPostCheck();
			}

		});
		CustomButton cancelBtn = new CustomButton("Cancel",AppConstants.ICON_CANCEL);
		final PostCheckWidget pcw = this;
		cancelBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				pcw.removeFromParent();
				callback.jsonifiedData("cancel");
			}

		});
		actionPanel.add(postBtn);
		actionPanel.add(cancelBtn);
		vPanel.add(postCheckInfoFlexTable);
		vPanel.add(proceduresInfoTable);
		vPanel.add(actionPanel);
		vPanel.setCellHorizontalAlignment(actionPanel, HasHorizontalAlignment.ALIGN_RIGHT);
		pids = new ArrayList<String>();
		loadSeletedProcedureInfo();
	}

	public void loadSeletedProcedureInfo() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {

		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { procs.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ClaimLog.getProceduresInfo",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {

						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								try {
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (result != null) {
										if (result.length > 0) {
											proceduresInfoTable
													.loadData(result);
										}
									}
								} catch (Exception e) {

								}
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}

		} else {
		}
	}

	public void prepareDataForPostCheck() {
		FlexTable procsInfoTable = proceduresInfoTable.getFlexTable();
		ArrayList<String> pays = new ArrayList<String>();
		ArrayList<String> copays = new ArrayList<String>();
		ArrayList<String> adjs = new ArrayList<String>();
		HashMap<String, String>[] procsMaps = new HashMap[pids.size()];
		float amount = 0;
		for (int i = 1; i < (pids.size() + 1); i++) {
			procsMaps[i - 1] = new HashMap<String, String>();
			// Window.alert(((TextBox)procsInfoTable.getWidget(i,
			// 6)).getText().toString());
			TextBox adjTb = (TextBox) procsInfoTable.getWidget(i, 6);
			TextBox payTb = (TextBox) procsInfoTable.getWidget(i, 8);
			TextBox copayTb = (TextBox) procsInfoTable.getWidget(i, 9);
			procsMaps[i - 1].put("proc", "" + pids.get(i - 1));
			if (adjTb.getText().equals("0") || adjTb.getText().equals("")) {
				procsMaps[i - 1].put("adj", "0");
			} else {
				procsMaps[i - 1].put("adj", adjTb.getText());
			}

			if (payTb.getText().equals("0") || payTb.getText().equals("")) {
				procsMaps[i - 1].put("pay", "0");
			} else {
				procsMaps[i - 1].put("pay", payTb.getText());
				try {
					float val = Float.parseFloat(payTb.getText());
					amount += val;
				} catch (Exception e) {

				}
			}

			if (copayTb.getText().equals("0") || copayTb.getText().equals("")) {
				procsMaps[i - 1].put("copay", "0");
			} else {
				procsMaps[i - 1].put("copay", copayTb.getText());
				try {
					float val = Float.parseFloat(copayTb.getText());
					amount += val;
				} catch (Exception e) {

				}
			}

		}
		float totalAmount = 0;
		try {
			totalAmount = Float.parseFloat(tbTotalAmount.getText());
		} catch (Exception e) {

		}
		if (amount != totalAmount) {
			Window
					.alert("The entered total amount is not equal to the amounts entered in individuall claims");
		} else {
			postCheck(procsMaps);
		}
	}

	public void postCheck(HashMap<String, String>[] maps) {
		final PostCheckWidget pcw=this;
		if (Util.getProgramMode() == ProgramMode.STUBBED) {

		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
//			String[] params = { "" + payerWidget.getStoredValue(),
//					JsonUtil.jsonify(tbCheckNo.getText()),
//					JsonUtil.jsonify(maps) };
			String[] params = { "" ,
					JsonUtil.jsonify(tbCheckNo.getText()),
					JsonUtil.jsonify(maps) };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.ClaimLog.post_check",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								pcw.removeFromParent();
								callback.jsonifiedData("update");
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}

		} else {
		}
	}

}
